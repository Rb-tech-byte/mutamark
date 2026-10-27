<?php

namespace App\Http\Controllers;

use App\Models\ServiceTicket;
use App\Models\User;
use App\Models\WarrantyRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\ProductService\Models\ProductServiceItem;

class WarrantyServicingController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-account')];
    }
    protected function getCreatorId()
    {
        if (function_exists('creatorId')) {
            $cid = creatorId();
            if ($cid) {
                return $cid;
            }
        }
        return Auth::check() ? Auth::id() : 1;
    }

    /**
     * Display Warranty & Servicing Management Dashboard
     */
    public function index(Request $request)
    {
        $creatorId = $this->getCreatorId();

        // Auto-expire warranties past end date
        WarrantyRegistration::where('status', 'active')
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        // Key Metrics
        $totalWarranties = WarrantyRegistration::count();
        $activeWarranties = WarrantyRegistration::where('status', 'active')->count();
        $expiredWarranties = WarrantyRegistration::where('status', 'expired')->count();
        $openTickets = ServiceTicket::whereNotIn('status', ['delivered_to_customer', 'cancelled'])->count();
        $vendorRepairs = ServiceTicket::where('vendor_repair', true)
            ->whereNotIn('status', ['delivered_to_customer', 'cancelled'])->count();
        $completedTickets = ServiceTicket::whereIn('status', ['repaired', 'delivered_to_customer'])->count();

        // Datasets
        $warranties = WarrantyRegistration::with(['invoice', 'serviceTickets'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($w) {
                $daysRemaining = max(0, (int) now()->diffInDays(Carbon::parse($w->end_date), false));
                $w->days_remaining = $daysRemaining;
                return $w;
            });

        $serviceTickets = ServiceTicket::with(['warranty', 'technician'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $products = ProductServiceItem::select('id', 'name', 'sku')->orderBy('name')->get();
        $technicians = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Warranty/Index', [
            'metrics' => [
                'total_warranties' => $totalWarranties,
                'active_warranties' => $activeWarranties,
                'expired_warranties' => $expiredWarranties,
                'open_tickets' => $openTickets,
                'vendor_repairs' => $vendorRepairs,
                'completed_tickets' => $completedTickets,
            ],
            'warranties' => $warranties,
            'service_tickets' => $serviceTickets,
            'products' => $products,
            'technicians' => $technicians,
        ]);
    }

    /**
     * Register a new Warranty Certificate
     */
    public function storeWarranty(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'product_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'warranty_period_months' => 'required|integer|min:1',
            'warranty_terms' => 'nullable|string',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = $start->copy()->addMonths((int) $validated['warranty_period_months']);
        $creatorId = $this->getCreatorId();

        $warranty = WarrantyRegistration::create([
            'warranty_code' => WarrantyRegistration::generateWarrantyCode(),
            'product_id' => $validated['product_id'] ?? null,
            'serial_number' => $validated['serial_number'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'warranty_period_months' => $validated['warranty_period_months'],
            'warranty_terms' => $validated['warranty_terms'] ?? null,
            'status' => $end->isPast() ? 'expired' : 'active',
            'creator_id' => Auth::id() ?: 1,
            'created_by' => $creatorId,
        ]);

        return redirect()->back()->with('success', "Warranty certificate registered successfully: {$warranty->warranty_code}");
    }

    /**
     * Create a Service & Repair Ticket (RMA)
     */
    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'service_type' => 'required|in:warranty_claim,paid_repair,preventive_maintenance',
            'issue_reported' => 'required|string',
            'vendor_repair' => 'nullable|boolean',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_tracking_ref' => 'nullable|string|max:255',
            'estimated_cost' => 'nullable|numeric|min:0',
            'assigned_technician_id' => 'nullable|integer',
        ]);

        $warrantyId = null;
        if (!empty($validated['serial_number'])) {
            $matchedWarranty = WarrantyRegistration::where('serial_number', $validated['serial_number'])
                ->where('status', 'active')
                ->where('end_date', '>=', now()->toDateString())
                ->first();

            if ($matchedWarranty) {
                $warrantyId = $matchedWarranty->id;
            }
        }

        $creatorId = $this->getCreatorId();

        $ticket = ServiceTicket::create([
            'ticket_number' => ServiceTicket::generateTicketNumber(),
            'warranty_id' => $warrantyId,
            'customer_name' => $validated['customer_name'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'product_name' => $validated['product_name'],
            'serial_number' => $validated['serial_number'] ?? null,
            'service_type' => $validated['service_type'],
            'issue_reported' => $validated['issue_reported'],
            'vendor_repair' => !empty($validated['vendor_repair']),
            'vendor_name' => $validated['vendor_name'] ?? null,
            'vendor_tracking_ref' => $validated['vendor_tracking_ref'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? 0,
            'actual_cost' => 0,
            'assigned_technician_id' => $validated['assigned_technician_id'] ?? null,
            'status' => !empty($validated['vendor_repair']) ? 'sent_to_vendor' : 'received',
            'creator_id' => Auth::id() ?: 1,
            'created_by' => $creatorId,
        ]);

        return redirect()->back()->with('success', "Service & Repair ticket opened: {$ticket->ticket_number}");
    }

    /**
     * Update Service Ticket status and cost
     */
    public function updateTicket(Request $request, $id)
    {
        $ticket = ServiceTicket::where('created_by', creatorId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:received,diagnosing,sent_to_vendor,repaired,delivered_to_customer,cancelled',
            'diagnostic_findings' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
            'vendor_tracking_ref' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (array_key_exists('diagnostic_findings', $validated)) {
            $updateData['diagnostic_findings'] = $validated['diagnostic_findings'];
        }
        if (array_key_exists('actual_cost', $validated)) {
            $updateData['actual_cost'] = $validated['actual_cost'];
        }
        if (array_key_exists('vendor_tracking_ref', $validated)) {
            $updateData['vendor_tracking_ref'] = $validated['vendor_tracking_ref'];
        }
        if (in_array($validated['status'], ['repaired', 'delivered_to_customer'])) {
            $updateData['completed_at'] = now();
        }

        $ticket->update($updateData);

        return redirect()->back()->with('success', "Ticket {$ticket->ticket_number} updated to " . ucfirst(str_replace('_', ' ', $validated['status'])));
    }

    /**
     * Real-time Warranty Verification Lookup API
     */
    public function lookup(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (empty($q)) {
            return response()->json(['found' => false, 'message' => 'Enter a serial number or certificate code.']);
        }

        $warranty = WarrantyRegistration::with(['invoice', 'serviceTickets'])
            ->where('serial_number', $q)
            ->orWhere('warranty_code', $q)
            ->orWhere('customer_phone', $q)
            ->first();

        if (!$warranty) {
            return response()->json([
                'found' => false,
                'message' => "No warranty registration found matching '{$q}'.",
            ]);
        }

        $daysRemaining = max(0, (int) now()->diffInDays(Carbon::parse($warranty->end_date), false));

        return response()->json([
            'found' => true,
            'warranty' => [
                'warranty_code' => $warranty->warranty_code,
                'serial_number' => $warranty->serial_number,
                'customer_name' => $warranty->customer_name,
                'customer_phone' => $warranty->customer_phone,
                'start_date' => $warranty->start_date->format('Y-m-d'),
                'end_date' => $warranty->end_date->format('Y-m-d'),
                'status' => $warranty->status,
                'days_remaining' => $daysRemaining,
                'is_active' => $warranty->status === 'active' && $daysRemaining > 0,
                'service_tickets_count' => $warranty->serviceTickets->count(),
            ],
        ]);
    }
}