<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\GrnItem;
use App\Models\PurchaseInvoice;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Workdo\Account\Models\Vendor;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class GoodsReceivedNoteController extends Controller
{
    /**
     * Display a listing of Goods Received Notes.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-grn')) {
            return back()->with('error', __('Permission denied.'));
        }

        $creatorId = creatorId();

        $query = GoodsReceivedNote::with(['vendor', 'warehouse', 'receiver', 'purchaseInvoice'])
            ->where(function($q) use ($creatorId) {
                if (Auth::user()->can('manage-any-grn')) {
                    $q->where('created_by', $creatorId);
                } else {
                    $q->where('created_by', $creatorId);
                }
            });

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('purchase_order_number', 'like', "%{$search}%")
                  ->orWhere('delivery_challan_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vq) use ($search) {
                      $vq->where('company_name', 'like', "%{$search}%")
                         ->orWhere('contact_person_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('vendor_id') && $request->vendor_id !== 'all') {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('inspection_status') && $request->inspection_status !== 'all') {
            $query->where('inspection_status', $request->inspection_status);
        }

        if ($request->filled('matching_status') && $request->matching_status !== 'all') {
            $query->where('matching_status', $request->matching_status);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Real-time KPI Stats
        $baseScope = GoodsReceivedNote::where('created_by', $creatorId);
        $totalGrns = (clone $baseScope)->count();
        $inspectedPassed = (clone $baseScope)->where('inspection_status', 'inspected_passed')->count();
        $pendingInspection = (clone $baseScope)->where('inspection_status', 'pending')->count();
        $matchedGrns = (clone $baseScope)->where('matching_status', 'matched')->count();
        $matchedValue = (float) (clone $baseScope)->where('matching_status', 'matched')->sum('total_amount');

        $grns = $query->latest('grn_date')->paginate($request->get('per_page', 10))->withQueryString();

        $vendors = Vendor::where('created_by', $creatorId)->get(['id', 'company_name', 'contact_person_name']);
        $warehouses = Warehouse::where('created_by', $creatorId)->get(['id', 'name', 'city']);
        $products = ProductServiceItem::where('created_by', $creatorId)->get(['id', 'name', 'sku', 'purchase_price']);

        return Inertia::render('Procurement/Grn/Index', [
            'grns' => $grns,
            'purchaseOrders' => \App\Models\PurchaseOrder::where('created_by', $creatorId)->with('items','vendor')->latest()->limit(100)->get(),
            'vendors' => $vendors,
            'warehouses' => $warehouses,
            'products' => $products,
            'stats' => [
                'total_grns' => $totalGrns,
                'inspected_passed' => $inspectedPassed,
                'pending_inspection' => $pendingInspection,
                'matched_grns' => $matchedGrns,
                'matched_value' => $matchedValue,
            ],
            'filters' => $request->only(['search', 'vendor_id', 'warehouse_id', 'inspection_status', 'matching_status', 'status']),
        ]);
    }

    /**
     * Store a newly created Goods Received Note.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('create-grn')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'purchase_order_id' => ['required', \Illuminate\Validation\Rule::exists('purchase_orders', 'id')->where('created_by', creatorId())->whereIn('status', ['issued','partially_received'])],
            'purchase_order_number' => 'nullable|string|max:50',
            'purchase_invoice_id' => ['nullable', \Illuminate\Validation\Rule::exists('purchase_invoices', 'id')->where('created_by', creatorId())],
            'vendor_id' => ['required', \Illuminate\Validation\Rule::exists('vendors', 'id')->where('created_by', creatorId())],
            'warehouse_id' => ['required', \Illuminate\Validation\Rule::exists('warehouses', 'id')->where('created_by', creatorId())],
            'grn_date' => 'required|date',
            'delivery_challan_number' => 'nullable|string|max:100',
            'carrier_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'quality_inspection_notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', \Illuminate\Validation\Rule::exists('product_service_items', 'id')->where('created_by', creatorId())],
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.accepted_quantity' => 'required|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.rejection_reason' => 'nullable|string|max:255',
        ]);

        $creatorId = creatorId();
        $authId = Auth::id();
        $order = \App\Models\PurchaseOrder::where('created_by', $creatorId)->with('items')->findOrFail($validated['purchase_order_id']);
        abort_unless((int) $validated['vendor_id'] === (int) $order->vendor_id && (int) $validated['warehouse_id'] === (int) $order->warehouse_id, 422, 'Select the supplier and warehouse from the purchase order.');
        $validated['purchase_order_number'] = $order->po_number;
        foreach ($validated['items'] as &$line) {
            $ordered = $order->items->firstWhere('product_id', $line['product_id']);
            abort_unless($ordered, 422, 'Every received product must belong to the purchase order.');
            $line['ordered_quantity'] = $ordered->quantity; $line['unit_price'] = $ordered->unit_price;
        }
        unset($line);

        // Generate sequential GRN number
        $year = date('Y');
        $lastGrn = GoodsReceivedNote::where('created_by', $creatorId)
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = 1;
        if ($lastGrn && preg_match('/GRN-\d{4}-(\d+)/', $lastGrn->grn_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }
        $grnNumber = \App\Services\DocumentNumberService::next('GRN');

        DB::beginTransaction();
        try {
            $totalOrdered = 0;
            $totalReceived = 0;
            $totalAccepted = 0;
            $totalRejected = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $ordered = (float) $item['ordered_quantity'];
                $recv = (float) $item['received_quantity'];
                $acc = (float) $item['accepted_quantity'];
                $rej = (float) ($item['rejected_quantity'] ?? ($recv - $acc));
                $price = (float) $item['unit_price'];

                $totalOrdered += $ordered;
                $totalReceived += $recv;
                $totalAccepted += $acc;
                $totalRejected += $rej;
                $totalAmount += ($acc * $price);
            }

            // Determine inspection status based on quantities
            $inspectionStatus = 'inspected_passed';
            if ($totalRejected > 0 && $totalAccepted > 0) {
                $inspectionStatus = 'partially_accepted';
            } elseif ($totalRejected > 0 && $totalAccepted == 0) {
                $inspectionStatus = 'rejected';
            }

            $grn = GoodsReceivedNote::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $order->id,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'purchase_invoice_id' => $validated['purchase_invoice_id'] ?? null,
                'vendor_id' => $validated['vendor_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'grn_date' => $validated['grn_date'],
                'delivery_challan_number' => $validated['delivery_challan_number'] ?? null,
                'carrier_name' => $validated['carrier_name'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'received_by' => $authId,
                'inspection_status' => $inspectionStatus,
                'matching_status' => 'unmatched',
                'total_ordered_qty' => $totalOrdered,
                'total_received_qty' => $totalReceived,
                'total_accepted_qty' => $totalAccepted,
                'total_rejected_qty' => $totalRejected,
                'total_amount' => $totalAmount,
                'quality_inspection_notes' => $validated['quality_inspection_notes'] ?? null,
                'status' => 'received',
                'creator_id' => $authId,
                'created_by' => $creatorId,
            ]);

            foreach ($validated['items'] as $item) {
                $acc = (float) $item['accepted_quantity'];
                $price = (float) $item['unit_price'];
                $rej = (float) ($item['rejected_quantity'] ?? 0);

                GrnItem::create([
                    'grn_id' => $grn->id,
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'accepted_quantity' => $acc,
                    'rejected_quantity' => $rej,
                    'unit_price' => $price,
                    'total_amount' => ($acc * $price),
                    'batch_number' => $item['batch_number'] ?? null,
                    'rejection_reason' => $item['rejection_reason'] ?? null,
                ]);
            }

            // Run 3-Way Match Check if linked to invoice
            $matchResult = $grn->computeThreeWayMatch();
            $grn->matching_status = $matchResult['match_status'];
            $grn->save();

            DB::commit();

            return redirect()->route('goods-received-notes.index')
                ->with('success', __("Goods Received Note {$grnNumber} generated successfully."));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('Failed to create GRN: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified Goods Received Note with 3-Way Matching Analysis.
     */
    public function show($id)
    {
        if (!Auth::user()->can('manage-grn')) {
            return back()->with('error', __('Permission denied.'));
        }

        $grn = GoodsReceivedNote::with([
            'vendor',
            'warehouse',
            'receiver',
            'purchaseInvoice.items.product',
            'items.product'
        ])->findOrFail($id);

        $matchingAnalysis = $grn->computeThreeWayMatch();

        return Inertia::render('Procurement/Grn/Show', [
            'grn' => $grn,
            'matching_analysis' => $matchingAnalysis,
        ]);
    }

    /**
     * Post GRN intake directly to warehouse inventory.
     */
    public function post($id)
    {
        \App\Services\CompanyAccess::requirePermission('post-grn');
        $grn = DB::transaction(function () use ($id) {
            $snapshot = GoodsReceivedNote::where('created_by', creatorId())->findOrFail($id);
            $invoice = $snapshot->purchase_invoice_id ? PurchaseInvoice::where('created_by', creatorId())->lockForUpdate()->findOrFail($snapshot->purchase_invoice_id) : null;
            $grn = GoodsReceivedNote::where('created_by', creatorId())->lockForUpdate()->findOrFail($id);
            if ($grn->status === 'posted') return $grn;
            abort_unless(in_array($grn->status, ['draft','received']), 409, 'Only active receipts may post.');
            abort_unless(in_array($grn->inspection_status, ['inspected_passed','partially_accepted']), 422, 'Complete quality inspection before posting.');
            $order = $grn->purchase_order_id ? \App\Models\PurchaseOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($grn->purchase_order_id) : null;
            abort_unless($order && in_array($order->status, ['issued','partially_received']), 422, 'A valid issued purchase order is required.');
            abort_unless((int) $order->vendor_id === (int) $grn->vendor_id && (int) $order->warehouse_id === (int) $grn->warehouse_id, 422, 'Purchase order supplier and warehouse must match.');
            if ($invoice) {
                $vendor = Vendor::where('created_by', creatorId())->findOrFail($grn->vendor_id);
                abort_unless((int) $vendor->user_id === (int) $invoice->vendor_id && (int) $grn->warehouse_id === (int) $invoice->warehouse_id, 422, 'Invoice supplier and warehouse must match.');
            }
            foreach ($grn->items->sortBy('product_id') as $item) {
                $otherReceipts = DB::table('grn_items as i')->join('goods_received_notes as n', 'n.id', '=', 'i.grn_id')->where('n.purchase_order_id', $order->id)->where('n.id','!=',$grn->id)->where('n.status','posted')->where('i.product_id',$item->product_id)->sum('i.accepted_quantity');
                $orderedQuantity = $order->items()->where('product_id',$item->product_id)->sum('quantity_ordered');
                abort_if((float) $otherReceipts + (float) $grn->items->where('product_id',$item->product_id)->sum('accepted_quantity') > (float) $orderedQuantity, 422, 'Receipt exceeds remaining PO quantity.');
                abort_if((float) $item->accepted_quantity > (float) $item->received_quantity || (float) $item->accepted_quantity < 0, 422, 'Accepted quantity must be within received quantity.');
                if ($invoice) {
                    $other = DB::table('grn_items as i')->join('goods_received_notes as n', 'n.id', '=', 'i.grn_id')->where('n.purchase_invoice_id', $invoice->id)->where('n.id', '!=', $grn->id)->where('n.status', 'posted')->where('i.product_id', $item->product_id)->sum('i.accepted_quantity');
                    $thisQty = $grn->items->where('product_id', $item->product_id)->sum('accepted_quantity');
                    abort_if((float) $other + (float) $thisQty > (float) $invoice->items()->where('product_id', $item->product_id)->sum('quantity'), 422, 'Receipt quantity exceeds invoice quantity.');
                }
                if ((float) $item->accepted_quantity > 0 && ($order || !$invoice || $invoice->status === 'draft')) {
                    $stock = WarehouseStock::firstOrCreate(['warehouse_id' => $grn->warehouse_id, 'product_id' => $item->product_id], ['quantity' => 0]);
                    $stock->increment('quantity', $item->accepted_quantity);
                }
            }
            $grn->update(['status' => 'posted']);
            $receivedTotal = DB::table('grn_items as i')->join('goods_received_notes as n','n.id','=','i.grn_id')->where('n.purchase_order_id',$order->id)->where('n.status','posted')->sum('i.accepted_quantity');
            $order->update(['status' => (float) $receivedTotal >= (float) $order->items()->sum('quantity_ordered') ? 'received' : 'partially_received']);
            return $grn;
        }, 3);
        return request()->expectsJson() ? response()->json($grn->load('items')) : back()->with('success', __('Receipt posted successfully.'));
    }

    public function purchaseOrderAction(Request $request, \App\Models\PurchaseOrder $purchaseOrder)
    {
        \App\Services\CompanyAccess::check($purchaseOrder);
        $data = $request->validate(['action' => 'required|in:issue,invoice,cancel']);
        if ($data['action'] === 'invoice') {
            $invoice = \App\Services\ThreeWayMatchService::invoicePurchaseOrder($purchaseOrder);
            return $request->expectsJson() ? response()->json($invoice) : redirect()->route('purchase-invoices.show', $invoice);
        }
        \App\Services\CompanyAccess::requirePermission('post-purchase-invoices');
        DB::transaction(function () use ($purchaseOrder,$data) {
            $order = \App\Models\PurchaseOrder::where('created_by',creatorId())->lockForUpdate()->findOrFail($purchaseOrder->id);
            if ($data['action'] === 'issue') {
                if ($order->status === 'issued') return;
                abort_unless($order->status === 'draft' && $order->items()->exists(),409,'Only complete drafts can issue.');
                $order->update(['status'=>'issued','approved_by'=>Auth::id(),'approved_at'=>now()]);
            } else {
                abort_unless(in_array($order->status,['draft','issued']) && !GoodsReceivedNote::where('purchase_order_id',$order->id)->exists(),409,'Received orders require a purchase return.');
                $order->update(['status'=>'cancelled']);
            }
        },3);
        return $request->expectsJson() ? response()->json($purchaseOrder->fresh()) : back()->with('success',__('Purchase order updated.'));
    }

    public function updateInspection(Request $request, $id)
    {
        if (!Auth::user()->can('inspect-grn')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'inspection_status' => 'required|in:inspected_passed,partially_accepted,rejected',
            'quality_inspection_notes' => 'nullable|string|max:1000',
            'rejection_notes' => 'nullable|string|max:1000',
        ]);

        $grn = GoodsReceivedNote::where('created_by', creatorId())->findOrFail($id);
        $grn->inspection_status = $validated['inspection_status'];
        $grn->quality_inspection_notes = $validated['quality_inspection_notes'] ?? null;
        $grn->rejection_notes = $validated['rejection_notes'] ?? null;
        $grn->save();

        return back()->with('success', __("Inspection status updated to {$validated['inspection_status']}."));
    }

    /**
     * API endpoint to retrieve 3-way match analysis for a GRN.
     */
    public function apiMatchCheck($grnId)
    {
        $grn = GoodsReceivedNote::with([
            'vendor',
            'warehouse',
            'purchaseInvoice.items',
            'items.product'
        ])->find($grnId);

        if (!$grn) {
            return response()->json(['success' => false, 'message' => 'GRN not found'], 404);
        }

        $analysis = $grn->computeThreeWayMatch();

        return response()->json([
            'success' => true,
            'grn_number' => $grn->grn_number,
            'purchase_order_number' => $grn->purchase_order_number,
            'supplier_invoice' => $grn->purchaseInvoice ? $grn->purchaseInvoice->invoice_number : null,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Remove the specified Goods Received Note.
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('delete-grn')) {
            return back()->with('error', __('Permission denied.'));
        }

        $grn = GoodsReceivedNote::where('created_by', creatorId())->findOrFail($id);

        if ($grn->status === 'posted') {
            return back()->with('error', __('Cannot delete an already posted GRN.'));
        }

        $grn->delete();

        return redirect()->route('goods-received-notes.index')
            ->with('success', __('Goods Received Note deleted successfully.'));
    }
}
