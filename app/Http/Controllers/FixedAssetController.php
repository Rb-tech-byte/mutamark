<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\AssetDisposal;
use App\Models\AssetTransfer;
use App\Models\FixedAsset;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Models\WarrantyRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FixedAssetController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-account')];
    }
    /**
     * Helper to safely retrieve creator / company ID
     */
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
     * Display a listing of Fixed Assets & Equipment Servicing.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCreatorId();

        // Top KPI metrics in TZS
        $totalAssets = FixedAsset::count();
        $totalCost = FixedAsset::sum('purchase_cost');
        $totalCurrentValue = FixedAsset::sum('current_value');
        $totalDepreciation = FixedAsset::sum('accumulated_depreciation');
        $activeWarrantiesCount = WarrantyRegistration::where('status', 'active')->count();
        $openTicketsCount = ServiceTicket::whereNotIn('status', ['delivered_to_customer', 'cancelled'])->count();

        // Datasets
        $assets = FixedAsset::with(['category', 'custodian'])
            ->orderBy('id', 'desc')
            ->get();

        $categories = AssetCategory::orderBy('name')->get();
        $custodians = User::select('id', 'name', 'email')->orderBy('name')->get();

        $depreciations = AssetDepreciation::with('asset')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $transfers = AssetTransfer::with(['asset', 'fromCustodian', 'toCustodian'])
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $disposals = AssetDisposal::with('asset')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $warranties = WarrantyRegistration::orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $serviceTickets = ServiceTicket::with(['warranty', 'technician'])
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('Assets/Index', [
            'metrics' => [
                'total_assets' => $totalAssets,
                'total_cost' => (float) $totalCost,
                'total_current_value' => (float) $totalCurrentValue,
                'total_depreciation' => (float) $totalDepreciation,
                'active_warranties_count' => $activeWarrantiesCount,
                'open_tickets_count' => $openTicketsCount,
            ],
            'assets' => $assets,
            'categories' => $categories,
            'custodians' => $custodians,
            'depreciations' => $depreciations,
            'transfers' => $transfers,
            'disposals' => $disposals,
            'warranties' => $warranties,
            'service_tickets' => $serviceTickets,
        ]);
    }

    /**
     * Store a newly created Fixed Asset.
     */
    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|in:straight_line,reducing_balance,none',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
            'useful_life_years' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'custodian_id' => 'nullable|exists:users,id',
            'serial_number' => 'nullable|string|max:100',
            'warranty_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $salvage = $validated['salvage_value'] ?? 0;
        $cost = $validated['purchase_cost'];
        $creatorId = $this->getCreatorId();

        $asset = FixedAsset::create([
            'asset_code' => FixedAsset::generateAssetCode(),
            'name' => $validated['name'],
            'asset_category_id' => $validated['asset_category_id'],
            'purchase_date' => $validated['purchase_date'],
            'purchase_cost' => $cost,
            'salvage_value' => $salvage,
            'current_value' => $cost,
            'accumulated_depreciation' => 0,
            'depreciation_method' => $validated['depreciation_method'],
            'depreciation_rate' => $validated['depreciation_rate'],
            'useful_life_years' => $validated['useful_life_years'],
            'location' => $validated['location'] ?? null,
            'custodian_id' => $validated['custodian_id'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'warranty_expiry_date' => $validated['warranty_expiry_date'] ?? null,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        // Auto-register warranty if serial number and warranty expiry are provided
        if (!empty($validated['serial_number']) && !empty($validated['warranty_expiry_date'])) {
            WarrantyRegistration::create([
                'warranty_code' => WarrantyRegistration::generateWarrantyCode(),
                'serial_number' => $validated['serial_number'],
                'customer_name' => 'Internal Asset Custody (' . ($asset->custodian ? $asset->custodian->name : 'Company') . ')',
                'start_date' => $validated['purchase_date'],
                'end_date' => $validated['warranty_expiry_date'],
                'warranty_period_months' => Carbon::parse($validated['purchase_date'])->diffInMonths($validated['warranty_expiry_date']),
                'warranty_terms' => 'OEM Standard Hardware Warranty for ' . $asset->name,
                'status' => Carbon::parse($validated['warranty_expiry_date'])->isPast() ? 'expired' : 'active',
                'creator_id' => Auth::id(),
                'created_by' => $creatorId,
            ]);
        }

        return redirect()->back()->with('success', 'Fixed Asset registered successfully with Code ' . $asset->asset_code . ' in TZS!');
    }

    /**
     * Run depreciation cycle for a specified period.
     */
    public function runDepreciation(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|max:50', // e.g. 2026-10
            'asset_id' => 'nullable|exists:fixed_assets,id',
        ]);

        $period = $validated['period'];
        $year = (int) substr($period, 0, 4);
        $creatorId = $this->getCreatorId();

        $query = FixedAsset::where('status', 'active');
        if (!empty($validated['asset_id'])) {
            $query->where('id', $validated['asset_id']);
        }

        $activeAssets = $query->get();
        $totalDepreciationPosted = 0;
        $count = 0;

        DB::transaction(function () use ($activeAssets, $period, $year, $creatorId, &$totalDepreciationPosted, &$count) {
            foreach ($activeAssets as $asset) {
                // Check if already depreciated for this period
                $existing = AssetDepreciation::where('asset_id', $asset->id)
                    ->where('period', $period)
                    ->first();

                if ($existing) {
                    continue;
                }

                $monthlyAmount = $asset->computeMonthlyDepreciation();
                if ($monthlyAmount <= 0) {
                    continue;
                }

                $openingValue = (float) $asset->current_value;
                $closingValue = max((float) $asset->salvage_value, $openingValue - $monthlyAmount);

                AssetDepreciation::create([
                    'asset_id' => $asset->id,
                    'fiscal_year' => $year,
                    'period' => $period,
                    'opening_value' => $openingValue,
                    'depreciation_amount' => $monthlyAmount,
                    'closing_value' => $closingValue,
                    'posted_by' => Auth::id(),
                    'posted_at' => now(),
                    'creator_id' => Auth::id(),
                    'created_by' => $creatorId,
                ]);

                $asset->update([
                    'current_value' => $closingValue,
                    'accumulated_depreciation' => (float) $asset->accumulated_depreciation + $monthlyAmount,
                ]);

                $totalDepreciationPosted += $monthlyAmount;
                $count++;
            }
        });

        return redirect()->back()->with('success', "Depreciation cycle for {$period} completed! Posted TZS " . number_format($totalDepreciationPosted, 2) . " across {$count} active assets.");
    }

    /**
     * Store an Asset Transfer.
     */
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:fixed_assets,id',
            'transfer_date' => 'required|date',
            'to_location' => 'required|string|max:255',
            'to_custodian_id' => 'nullable|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        $asset = FixedAsset::where('created_by', creatorId())->findOrFail($validated['asset_id']);
        $creatorId = $this->getCreatorId();

        $transfer = AssetTransfer::create([
            'transfer_number' => AssetTransfer::generateTransferNumber(),
            'asset_id' => $asset->id,
            'transfer_date' => $validated['transfer_date'],
            'from_location' => $asset->location,
            'to_location' => $validated['to_location'],
            'from_custodian_id' => $asset->custodian_id,
            'to_custodian_id' => $validated['to_custodian_id'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'approved_by' => Auth::id(),
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        $asset->update([
            'location' => $validated['to_location'],
            'custodian_id' => $validated['to_custodian_id'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Asset transferred successfully with Reference ' . $transfer->transfer_number);
    }

    /**
     * Store an Asset Disposal / Write-off.
     */
    public function storeDisposal(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:fixed_assets,id',
            'disposal_date' => 'required|date',
            'disposal_type' => 'required|in:sale,scrapped,donated,write_off',
            'sale_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
        ]);

        $asset = FixedAsset::where('created_by', creatorId())->findOrFail($validated['asset_id']);
        $bookValue = (float) $asset->current_value;
        $saleAmount = (float) ($validated['sale_amount'] ?? 0);
        $gainLoss = $saleAmount - $bookValue;
        $creatorId = $this->getCreatorId();

        $disposal = AssetDisposal::create([
            'disposal_number' => AssetDisposal::generateDisposalNumber(),
            'asset_id' => $asset->id,
            'disposal_date' => $validated['disposal_date'],
            'disposal_type' => $validated['disposal_type'],
            'sale_amount' => $saleAmount,
            'book_value_at_disposal' => $bookValue,
            'gain_loss_amount' => $gainLoss,
            'reason' => $validated['reason'] ?? null,
            'approved_by' => Auth::id(),
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        $asset->update([
            'status' => $validated['disposal_type'] === 'write_off' ? 'written_off' : 'disposed',
            'current_value' => 0,
        ]);

        return redirect()->back()->with('success', 'Asset disposal recorded. Book Value: TZS ' . number_format($bookValue, 2) . ', Gain/Loss: TZS ' . number_format($gainLoss, 2));
    }

    /**
     * Store Warranty Registration.
     */
    public function storeWarranty(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'start_date' => 'required|date',
            'warranty_period_months' => 'required|integer|min:1',
            'warranty_terms' => 'nullable|string',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = $start->copy()->addMonths((int) $validated['warranty_period_months']);
        $creatorId = $this->getCreatorId();

        $warranty = WarrantyRegistration::create([
            'warranty_code' => WarrantyRegistration::generateWarrantyCode(),
            'serial_number' => $validated['serial_number'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'warranty_period_months' => $validated['warranty_period_months'],
            'warranty_terms' => $validated['warranty_terms'] ?? null,
            'status' => $end->isPast() ? 'expired' : 'active',
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        return redirect()->back()->with('success', 'Warranty certificate registered: ' . $warranty->warranty_code);
    }

    /**
     * Store Service & Repair Ticket (RMA).
     */
    public function storeServiceTicket(Request $request)
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
            'estimated_cost' => 'nullable|numeric|min:0',
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
            'estimated_cost' => $validated['estimated_cost'] ?? 0,
            'actual_cost' => 0,
            'status' => !empty($validated['vendor_repair']) ? 'sent_to_vendor' : 'received',
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        return redirect()->back()->with('success', 'Service Repair Ticket created: ' . $ticket->ticket_number);
    }

    /**
     * Update Service Ticket Status and Resolution Cost.
     */
    public function updateTicketStatus(Request $request, $id)
    {
        $ticket = ServiceTicket::where('created_by', creatorId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:received,diagnosing,sent_to_vendor,repaired,delivered_to_customer,cancelled',
            'diagnostic_findings' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (isset($validated['diagnostic_findings'])) {
            $updateData['diagnostic_findings'] = $validated['diagnostic_findings'];
        }

        if (isset($validated['actual_cost'])) {
            $updateData['actual_cost'] = $validated['actual_cost'];
        }

        if ($validated['status'] === 'delivered_to_customer' || $validated['status'] === 'repaired') {
            $updateData['completed_at'] = now();
        }

        $ticket->update($updateData);

        return redirect()->back()->with('success', 'Ticket ' . $ticket->ticket_number . ' updated to ' . ucfirst(str_replace('_', ' ', $validated['status'])));
    }
}
