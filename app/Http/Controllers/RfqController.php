<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\SupplierQuotation;
use App\Models\Warehouse;
use App\Services\RfqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Account\Models\Vendor;
use Workdo\ProductService\Models\ProductServiceItem;

class RfqController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array { return [new \Illuminate\Routing\Controllers\Middleware('can:manage-purchase-invoices')]; }
    /**
     * Get authenticated creator/company ID safely.
     */
    protected function getCompanyId(): int
    {
        if (function_exists('creatorId')) {
            $cid = creatorId();
            if ($cid) {
                return (int) $cid;
            }
        }
        return Auth::check() ? Auth::id() : 1;
    }

    /**
     * Display a listing of Requests for Quotation (RFQ).
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = Rfq::with([
            'items.product',
            'quotations.items.rfqItem',
            'warehouse',
            'requisition',
            'awardedVendor',
            'awardedPo',
        ])->where('created_by', $companyId);

        // Search filter
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('rfq_number', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhereHas('items', function ($iq) use ($term) {
                        $iq->where('product_name', 'like', "%{$term}%");
                    });
            });
        }

        // Warehouse filter
        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Status tab filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rfqs = $query->orderBy('id', 'desc')->get();

        // Calculate KPI metrics
        $allRfqs = Rfq::with(['quotations'])->where('created_by', $companyId)->get();
        $totalRfqs = $allRfqs->count();
        $publishedCount = $allRfqs->where('status', 'published')->count();
        $evaluatingCount = $allRfqs->where('status', 'evaluating')->count();
        $awardedCount = $allRfqs->where('status', 'awarded')->count();
        
        $totalAwardedValue = $allRfqs->where('status', 'awarded')->sum(function ($r) {
            $win = $r->quotations->where('status', 'awarded')->first();
            return $win ? (float)$win->total_bid_amount : 0;
        });

        $warehouses = Warehouse::where('created_by', $companyId)->get(['id', 'name']);
        $vendors = Vendor::where('created_by', $companyId)->get(['id', 'company_name', 'contact_person_name']);
        $products = ProductServiceItem::where('created_by', $companyId)->get(['id', 'name', 'sku', 'purchase_price']);
        
        // Approved requisitions that can be converted to RFQ
        $approvedRequisitions = PurchaseRequisition::with(['items'])
            ->where('created_by', $companyId)
            ->whereIn('status', ['approved', 'converted_to_rfq'])
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Procurement/Rfq/Index', [
            'rfqs' => $rfqs,
            'kpi' => [
                'total_rfqs' => $totalRfqs,
                'published_count' => $publishedCount,
                'evaluating_count' => $evaluatingCount,
                'awarded_count' => $awardedCount,
                'total_awarded_tzs' => (float) $totalAwardedValue,
            ],
            'warehouses' => $warehouses,
            'vendors' => $vendors,
            'products' => $products,
            'approved_requisitions' => $approvedRequisitions,
            'filters' => $request->only(['search', 'warehouse_id', 'status']),
        ]);
    }

    /**
     * Store a newly created RFQ.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'closing_date' => 'required|date',
            'delivery_deadline' => 'nullable|date',
            'warehouse_id' => 'nullable|integer',
            'requisition_id' => 'nullable|integer',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
            'evaluation_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string',
            'items.*.sku' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.target_unit_price' => 'nullable|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $userId = Auth::id() ?: 1;

        $rfq = RfqService::createRfq($validated, $userId, $companyId);

        return redirect()->back()->with('success', "Request for Quotation {$rfq->rfq_number} created successfully.");
    }

    /**
     * Update an existing RFQ.
     */
    public function update(Request $request, Rfq $rfq)
    {
        \App\Services\CompanyAccess::check($rfq);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'closing_date' => 'required|date',
            'delivery_deadline' => 'nullable|date',
            'warehouse_id' => 'nullable|integer',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
            'evaluation_notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string',
            'items.*.sku' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.target_unit_price' => 'nullable|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        $updated = RfqService::updateRfq($rfq, $validated);

        return redirect()->back()->with('success', "RFQ {$updated->rfq_number} updated successfully.");
    }

    /**
     * Publish RFQ for open supplier bidding.
     */
    public function publish(Rfq $rfq)
    {
        \App\Services\CompanyAccess::check($rfq);
        RfqService::publishRfq($rfq);

        return redirect()->back()->with('success', "RFQ {$rfq->rfq_number} published for competitive supplier bidding.");
    }

    /**
     * Record / Store a Supplier Quotation Bid.
     */
    public function storeBid(Request $request, Rfq $rfq)
    {
        \App\Services\CompanyAccess::check($rfq);
        $validated = $request->validate([
            'vendor_id' => 'required|integer',
            'quotation_reference' => 'required|string|max:255',
            'submission_date' => 'required|date',
            'delivery_lead_time_days' => 'required|integer|min:1',
            'warranty_months' => 'nullable|integer|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.rfq_item_id' => 'required|integer',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.remarks' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $userId = Auth::id() ?: 1;

        $bid = RfqService::addVendorBid($rfq, $validated, $userId, $companyId);

        return redirect()->back()->with('success', "Quotation bid from {$bid->vendor_name} ({$bid->quotation_reference}) recorded successfully.");
    }

    /**
     * Update an existing Supplier Quotation Bid.
     */
    public function updateBid(Request $request, SupplierQuotation $quotation)
    {
        \App\Services\CompanyAccess::check($quotation);
        $validated = $request->validate([
            'quotation_reference' => 'required|string|max:255',
            'delivery_lead_time_days' => 'required|integer|min:1',
            'warranty_months' => 'nullable|integer|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.rfq_item_id' => 'required|integer',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.remarks' => 'nullable|string',
        ]);

        $updated = RfqService::updateVendorBid($quotation, $validated);

        return redirect()->back()->with('success', "Supplier quotation {$updated->quotation_reference} updated successfully.");
    }

    /**
     * Delete a Supplier Quotation Bid.
     */
    public function destroyBid(SupplierQuotation $quotation)
    {
        \App\Services\CompanyAccess::check($quotation);
        RfqService::deleteVendorBid($quotation);

        return redirect()->back()->with('success', "Supplier quotation removed.");
    }

    /**
     * Award winning quotation and generate Purchase Order / Bill.
     */
    public function award(Request $request, Rfq $rfq)
    {
        \App\Services\CompanyAccess::check($rfq);
        $validated = $request->validate([
            'quotation_id' => 'required|integer|exists:supplier_quotations,id',
        ]);

        $userId = Auth::id() ?: 1;

        $result = RfqService::awardBidAndGeneratePo($rfq, (int) $validated['quotation_id'], $userId);

        $poNumber = $result['purchase_order']->po_number;

        return redirect()->back()->with('success', "RFQ {$rfq->rfq_number} successfully awarded! Purchase Order {$poNumber} generated and committed.");
    }

    /**
     * Delete an RFQ.
     */
    public function destroy(Rfq $rfq)
    {
        \App\Services\CompanyAccess::check($rfq);
        if ($rfq->status === 'awarded') {
            return redirect()->back()->with('error', 'Cannot delete an RFQ that has already been awarded to a Purchase Order.');
        }

        RfqService::deleteRfq($rfq);

        return redirect()->back()->with('success', "RFQ {$rfq->rfq_number} deleted successfully.");
    }
}
