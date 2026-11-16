<?php

namespace App\Http\Controllers;

use App\Models\ClearingForwarding\CFBorderMovement;
use App\Models\ClearingForwarding\CFCustomsRule;
use App\Models\ClearingForwarding\CFFreightBooking;
use App\Models\ClearingForwarding\CFJob;
use App\Models\ClearingForwarding\CFTrackingEvent;
use App\Models\ClearingForwarding\CFTransportOrder;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Workdo\ProductService\Models\ProductServiceItem;

class ClearingExtensionsController extends Controller
{
    public function borderIndex(Request $request)
    {
        $creatorId = creatorId();
        $movements = CFBorderMovement::with('job:id,job_number,title')
            ->where('created_by', $creatorId)
            ->latest()
            ->get();

        $jobs = CFJob::where('created_by', $creatorId)
            ->orderByDesc('id')
            ->get(['id', 'job_number', 'title']);

        return Inertia::render('ClearingForwarding/Border/Index', [
            'movements' => $movements,
            'jobs' => $jobs,
        ]);
    }

    public function storeBorder(Request $request)
    {
        $validated = $request->validate([
            'cf_job_id' => 'nullable|integer',
            'corridor' => 'required|string|max:120',
            'truck_registration' => 'required|string|max:60',
            'trailer_number' => 'nullable|string|max:60',
            'driver_name' => 'nullable|string|max:120',
            'cargo_type' => 'nullable|string|max:120',
            'movement_type' => 'required|in:import,export,transit,empty_return',
            'origin_country' => 'nullable|string|max:80',
            'destination_country' => 'nullable|string|max:80',
            'border_entry_at' => 'nullable|date',
            'customs_reference' => 'nullable|string|max:100',
            'rotation_number' => 'nullable|string|max:100',
            'transit_doc_number' => 'nullable|string|max:100',
            'gate_pass_number' => 'nullable|string|max:100',
            'border_expenses' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'delay_reason' => 'nullable|string',
        ]);

        CFBorderMovement::create(array_merge($validated, [
            'movement_number' => 'BRD-' . date('Y') . '-' . str_pad((string) (CFBorderMovement::count() + 1), 5, '0', STR_PAD_LEFT),
            'clearance_status' => 'arrived',
            'creator_id' => Auth::id(),
            'created_by' => creatorId(),
        ]));

        return back()->with('success', __('Border movement recorded successfully.'));
    }

    public function updateBorderStatus(Request $request, CFBorderMovement $movement)
    {
        if ((int) $movement->created_by !== (int) creatorId()) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'clearance_status' => 'required|in:arrived,customs_processing,held,released,exited',
            'delay_reason' => 'nullable|string',
            'gate_pass_number' => 'nullable|string|max:100',
        ]);

        if ($validated['clearance_status'] === 'exited' && !$movement->border_exit_at) {
            $validated['border_exit_at'] = now();
        }

        $movement->update($validated);
        return back()->with('success', __('Border clearance status updated.'));
    }

    public function freightIndex()
    {
        $creatorId = creatorId();
        $bookings = CFFreightBooking::with('job:id,job_number,title')
            ->where('created_by', $creatorId)
            ->latest()
            ->get();

        $jobs = CFJob::where('created_by', $creatorId)
            ->orderByDesc('id')
            ->get(['id', 'job_number', 'title']);

        $customsRules = CFCustomsRule::where('created_by', $creatorId)
            ->orderBy('cpc_code')
            ->get();

        return Inertia::render('ClearingForwarding/Freight/Index', [
            'bookings' => $bookings,
            'jobs' => $jobs,
            'customsRules' => $customsRules,
        ]);
    }

    public function storeFreight(Request $request)
    {
        $validated = $request->validate([
            'cf_job_id' => 'nullable|integer',
            'freight_mode' => 'required|in:sea,air,road,rail,multimodal',
            'shipping_line_carrier' => 'nullable|string|max:150',
            'vessel_flight_voyage' => 'nullable|string|max:120',
            'port_of_loading' => 'nullable|string|max:120',
            'port_of_discharge' => 'nullable|string|max:120',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
            'delivery_order_number' => 'nullable|string|max:80',
            'freight_cost' => 'nullable|numeric|min:0',
            'port_handling_charges' => 'nullable|numeric|min:0',
            'free_days' => 'nullable|integer|min:0',
            'demurrage_days' => 'nullable|integer|min:0',
            'demurrage_amount' => 'nullable|numeric|min:0',
            'detention_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'shipping_instructions' => 'nullable|string',
        ]);

        CFFreightBooking::create(array_merge($validated, [
            'booking_number' => 'FRT-' . date('Y') . '-' . str_pad((string) (CFFreightBooking::count() + 1), 5, '0', STR_PAD_LEFT),
            'status' => 'booked',
            'creator_id' => Auth::id(),
            'created_by' => creatorId(),
        ]));

        return back()->with('success', __('Freight booking created successfully.'));
    }

    public function updateFreightStatus(Request $request, CFFreightBooking $booking)
    {
        if ((int) $booking->created_by !== (int) creatorId()) {
            return back()->with('error', __('Permission denied.'));
        }
        $validated = $request->validate([
            'status' => 'required|in:quoted,booked,in_transit,arrived,do_issued,handed_over',
        ]);
        $booking->update($validated);
        return back()->with('success', __('Freight booking status updated.'));
    }

    public function storeCustomsRule(Request $request)
    {
        $validated = $request->validate([
            'cpc_code' => 'required|string|max:30',
            'procedure_name' => 'required|string|max:150',
            'country_code' => 'nullable|string|max:10',
            'import_duty_rate' => 'required|numeric|min:0|max:100',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'excise_duty_rate' => 'nullable|numeric|min:0|max:100',
            'rdl_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        CFCustomsRule::create(array_merge($validated, [
            'country_code' => $validated['country_code'] ?? 'TZ',
            'excise_duty_rate' => $validated['excise_duty_rate'] ?? 0,
            'rdl_rate' => $validated['rdl_rate'] ?? 1.5,
            'is_active' => true,
            'created_by' => creatorId(),
        ]));

        return back()->with('success', __('Customs CPC rule saved.'));
    }

    /**
     * Transfer cleared shipment directly to Fleet & Transport Management with truck/driver overlap prevention.
     */
    public function transferToFleet(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'vehicle_registration' => 'required|string|max:60',
            'driver_name' => 'required|string|max:120',
            'origin' => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'departure_date' => 'nullable|date',
        ]);

        // Business Rules 6 & 7: Prevent overlapping active trips for same vehicle or driver
        $overlap = CFTransportOrder::where('created_by', creatorId())
            ->whereIn('status', ['assigned', 'in_transit', 'at_border'])
            ->where(function ($q) use ($validated) {
                $q->where('vehicle_number', $validated['vehicle_registration'])
                    ->orWhere('driver_name', $validated['driver_name']);
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', __('Selected truck or driver is already assigned to an active trip.'));
        }

        CFTransportOrder::create([
            'cf_job_id' => $cfJob->id,
            'order_number' => 'TRP-' . date('Y') . '-' . str_pad((string) (CFTransportOrder::count() + 1), 5, '0', STR_PAD_LEFT),
            'vehicle_number' => $validated['vehicle_registration'],
            'driver_name' => $validated['driver_name'],
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'dispatch_date' => $validated['departure_date'] ?? now()->toDateString(),
            'status' => 'assigned',
            'creator_id' => Auth::id(),
            'created_by' => creatorId(),
        ]);

        CFTrackingEvent::create([
            'cf_job_id' => $cfJob->id,
            'milestone_code' => 'fleet_assigned',
            'location_name' => $validated['origin'],
            'event_time' => now(),
            'remarks' => "Transferred to Fleet: Truck {$validated['vehicle_registration']}, Driver {$validated['driver_name']}",
            'is_customer_visible' => true,
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', __('Shipment transferred to Fleet Management successfully.'));
    }

    /**
     * Generate Customer Sales Invoice directly from Clearing & Forwarding Job Charges.
     */
    public function generateInvoice(CFJob $cfJob)
    {
        if (!$cfJob->customer_id) {
            return back()->with('error', __('Shipment must be linked to a customer before generating an invoice.'));
        }

        $cfJob->loadMissing(['chargeEstimates', 'disbursements']);
        $totalCharges = (float) $cfJob->chargeEstimates->sum('amount') + (float) $cfJob->disbursements->sum('amount');
        if ($totalCharges <= 0) {
            $totalCharges = (float) ($cfJob->estimated_revenue ?? 0);
        }

        if ($totalCharges <= 0) {
            return back()->with('error', __('No billable charges or disbursements found on this shipment.'));
        }

        $invoice = DB::transaction(function () use ($cfJob, $totalCharges) {
            $inv = SalesInvoice::create([
                'invoice_number' => SalesInvoice::generateInvoiceNumber(),
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'customer_id' => $cfJob->customer_id,
                'subtotal' => $totalCharges,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $totalCharges,
                'paid_amount' => 0,
                'balance_amount' => $totalCharges,
                'status' => 'draft',
                'notes' => 'Generated from Clearing & Forwarding Job ' . $cfJob->job_number,
                'creator_id' => Auth::id(),
                'created_by' => creatorId(),
            ]);

            $firstProduct = ProductServiceItem::where('created_by', creatorId())->first();
            if ($firstProduct) {
                SalesInvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'product_id' => $firstProduct->id,
                    'quantity' => 1,
                    'unit_price' => $totalCharges,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $totalCharges,
                ]);
            }

            return $inv;
        });

        return back()->with('success', __('Customer invoice :num generated from shipment :job.', [
            'num' => $invoice->invoice_number,
            'job' => $cfJob->job_number,
        ]));
    }
}

