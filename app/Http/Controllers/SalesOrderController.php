<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesProposal;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Workdo\ProductService\Models\ProductServiceItem;

class SalesOrderController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-sales-invoices')];
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
     * Display listing of Sales Orders (Â§3.9.1, Â§3.9.4)
     */
    public function index(Request $request)
    {
        $creatorId = $this->getCreatorId();

        $query = SalesOrder::with(['customer', 'warehouse', 'proposal', 'items.product'])
            ->where('created_by', $creatorId);

        // Search
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', function ($cq) use ($term) {
                        $cq->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($term) {
                        $iq->where('product_name', 'like', "%{$term}%");
                    });
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Calculate KPI Metrics
        $allOrders = SalesOrder::where('created_by', $creatorId)->get();
        $totalOrders = $allOrders->count();
        $confirmedCount = $allOrders->where('status', 'confirmed')->count();
        $stockReservedCount = $allOrders->where('stock_reserved', true)->count();
        $fulfilledCount = $allOrders->where('status', 'fulfilled')->count();
        $totalOrderValue = $allOrders->sum('total_amount');

        $customers = User::where('created_by', creatorId())->whereIn('type', ['customer','client'])->select('id', 'name', 'email', 'mobile_no')->get();
        $warehouses = Warehouse::where('created_by', creatorId())->select('id', 'name')->get();
        $products = ProductServiceItem::select('id', 'name', 'sku', 'sale_price')->get();

        // Approved proposals available to convert to SO
        $convertibleProposals = SalesProposal::where('created_by', $creatorId)
            ->whereIn('status', ['accepted', 'approved'])
            ->with(['customer', 'items'])
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        if ($request->expectsJson()) return response()->json($orders);
        return Inertia::render('SalesOrder/Index', [
            'orders' => $orders,
            'metrics' => [
                'total_orders' => $totalOrders,
                'confirmed_count' => $confirmedCount,
                'stock_reserved_count' => $stockReservedCount,
                'fulfilled_count' => $fulfilledCount,
                'total_order_value' => (float) $totalOrderValue,
            ],
            'customers' => $customers,
            'warehouses' => $warehouses,
            'products' => $products,
            'convertible_proposals' => $convertibleProposals,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Store newly created Sales Order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', \Illuminate\Validation\Rule::exists('users', 'id')->where('created_by', creatorId())->whereIn('type', ['client','customer'])],
            'warehouse_id' => ['required', \Illuminate\Validation\Rule::exists('warehouses', 'id')->where('created_by', creatorId())->where('is_active', true)],
            'proposal_id' => 'nullable|integer',
            'order_date' => 'required|date',
            'delivery_due_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'distinct', \Illuminate\Validation\Rule::exists('product_service_items', 'id')->where('created_by', creatorId())],
            'items.*.product_name' => 'required|string|max:255',
            'items.*.sku' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        $creatorId = $this->getCreatorId();

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $disc = (float) ($item['discount_amount'] ?? 0);
                $tax = (float) ($item['tax_amount'] ?? 0);

                $lineSubtotal = $qty * $price;
                $subtotal += $lineSubtotal;
                $discountAmount += $disc;
                $taxAmount += $tax;
            }

            $totalAmount = max(0, $subtotal - $discountAmount + $taxAmount);

            $salesOrder = SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'proposal_id' => $validated['proposal_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'order_date' => $validated['order_date'],
                'delivery_due_date' => $validated['delivery_due_date'] ?? null,
                'status' => 'confirmed',
                'stock_reserved' => false,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_terms' => $validated['payment_terms'] ?? 'Due on Receipt',
                'notes' => $validated['notes'] ?? null,
                'creator_id' => Auth::id() ?: 1,
                'created_by' => $creatorId,
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $disc = (float) ($item['discount_amount'] ?? 0);
                $tax = (float) ($item['tax_amount'] ?? 0);
                $lineSubtotal = $qty * $price;
                $lineTotal = max(0, $lineSubtotal - $disc + $tax);

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $qty,
                    'fulfilled_quantity' => 0,
                    'unit_price' => $price,
                    'discount_amount' => $disc,
                    'tax_amount' => $tax,
                    'subtotal' => $lineSubtotal,
                    'total_amount' => $lineTotal,
                ]);
            }

            DB::commit();

            return $request->expectsJson() ? response()->json($salesOrder->load('items'), 201) : back()->with('success', __('Sales order created. Reserve stock when ready.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Failed to create sales order: " . $e->getMessage());
        }
    }

    /**
     * Convert an Approved Proposal directly to a Sales Order
     */
    public function convertProposalToOrder(Request $request, $proposalId)
    {
        $proposal = SalesProposal::where('created_by', creatorId())->with('items')->findOrFail($proposalId);
        $creatorId = $this->getCreatorId();

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'proposal_id' => $proposal->id,
                'customer_id' => $proposal->customer_id,
                'warehouse_id' => $proposal->warehouse_id,
                'order_date' => now()->toDateString(),
                'delivery_due_date' => $proposal->due_date ? $proposal->due_date->toDateString() : now()->addDays(7)->toDateString(),
                'status' => 'confirmed',
                'stock_reserved' => false,
                'subtotal' => $proposal->subtotal,
                'tax_amount' => $proposal->tax_amount,
                'discount_amount' => $proposal->discount_amount,
                'total_amount' => $proposal->total_amount,
                'payment_terms' => $proposal->payment_terms ?? 'Net 30',
                'notes' => "Converted from Quotation {$proposal->proposal_number}",
                'creator_id' => Auth::id() ?: 1,
                'created_by' => $creatorId,
            ]);

            foreach ($proposal->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?? 'Product Item',
                    'sku' => $item->sku ?? null,
                    'quantity' => $item->quantity,
                    'fulfilled_quantity' => 0,
                    'unit_price' => $item->price,
                    'discount_amount' => $item->discount_amount ?? 0,
                    'tax_amount' => $item->tax_amount ?? 0,
                    'subtotal' => $item->subtotal ?? ($item->quantity * $item->price),
                    'total_amount' => $item->total_amount ?? ($item->quantity * $item->price),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "Quotation {$proposal->proposal_number} successfully converted to Sales Order {$salesOrder->order_number} with stock reservation!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Conversion failed: " . $e->getMessage());
        }
    }

    /**
     * Toggle Stock Reservation
     */
    public function toggleStockReservation($id)
    {
        $order = SalesOrder::where('created_by', creatorId())->findOrFail($id);
        $order->reserve(!$order->stock_reserved);
        return request()->expectsJson() ? response()->json($order->fresh()) : back()->with('success', __('Stock reservation updated.'));
    }

    /**
     * Convert Sales Order into Delivery Note (DN)
     */
    public function convertToDeliveryNote(Request $request, $id)
    {
        $data = $request->validate(['items' => 'sometimes|array|min:1', 'items.*.id' => 'required|integer|distinct', 'items.*.quantity' => 'required|numeric|min:0.0001', 'delivery_address' => 'nullable|string|max:500']);
        $delivery = DB::transaction(function () use ($id, $data) {
            $order = SalesOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($id);
            abort_if(in_array($order->status, ['cancelled','fulfilled']), 409, 'This order has no pending fulfillment.');
            $lines = $order->items()->whereColumn('fulfilled_quantity', '<', 'quantity')->get();
            $selected = isset($data['items']) ? collect($data['items'])->keyBy('id') : null;
            if ($selected) abort_unless($selected->keys()->diff($lines->pluck('id'))->isEmpty(), 422, 'Select pending items from this order.');
            $items = $lines->filter(fn ($line) => !$selected || $selected->has($line->id))->map(function ($line) use ($selected) {
                $remaining = (float) $line->quantity - (float) $line->fulfilled_quantity;
                $quantity = $selected ? (float) $selected[$line->id]['quantity'] : $remaining;
                abort_if($quantity > $remaining, 422, 'Delivery exceeds remaining quantity.');
                return ['product_id' => $line->product_id, 'product_name' => $line->product_name, 'sku' => $line->sku, 'quantity_ordered' => $line->quantity, 'quantity_shipped' => $quantity];
            })->values()->all();
            abort_unless(count($items), 422, 'No pending items selected.');
            $invoice = SalesInvoice::where('sales_order_id', $order->id)->first();
            return \App\Services\DeliveryNoteService::createDeliveryNote(['sales_order_id' => $order->id, 'invoice_id' => $invoice?->id, 'customer_id' => $order->customer_id, 'warehouse_id' => $order->warehouse_id, 'delivery_date' => now()->toDateString(), 'shipping_address' => $data['delivery_address'] ?? null, 'status' => 'dispatched', 'items' => $items, 'notes' => 'Sales order '.$order->order_number], creatorId(), Auth::id());
        }, 3);
        return $request->expectsJson() ? response()->json($delivery, 201) : back()->with('success', __('Delivery dispatched successfully.'));
    }

    /**
     * Convert Sales Order into Formal Sales Invoice (INV)
     */
    public function convertToInvoice(Request $request, $id)
    {
        \App\Services\CompanyAccess::requirePermission('create-sales-invoices');
        $invoice = DB::transaction(function () use ($id) {
            $order = SalesOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($id);
            abort_if($order->status === 'cancelled', 409, 'A cancelled order cannot be invoiced.');
            $existing = SalesInvoice::where('created_by', creatorId())->where('sales_order_id', $order->id)->first();
            if ($existing) return $existing;
            $invoice = SalesInvoice::create(['invoice_number' => SalesInvoice::generateInvoiceNumber(), 'sales_order_id' => $order->id, 'customer_id' => $order->customer_id, 'warehouse_id' => $order->warehouse_id, 'invoice_date' => now()->toDateString(), 'due_date' => $order->delivery_due_date ?: now()->addDays(14)->toDateString(), 'status' => 'draft', 'type' => 'product', 'currency' => 'TZS', 'creator_id' => Auth::id(), 'created_by' => creatorId(), 'notes' => 'Sales order '.$order->order_number]);
            foreach ($order->items as $line) {
                $subtotal = (float) $line->quantity * (float) $line->unit_price;
                $discount = $subtotal > 0 ? (float) $line->discount_amount / $subtotal * 100 : 0;
                $taxBase = $subtotal - (float) $line->discount_amount;
                $tax = $taxBase > 0 ? (float) $line->tax_amount / $taxBase * 100 : 0;
                $invoice->items()->create(['product_id' => $line->product_id, 'quantity' => $line->quantity, 'unit_price' => $line->unit_price, 'discount_percentage' => $discount, 'tax_percentage' => $tax]);
            }
            $items = $invoice->items()->get();
            $invoice->update(['subtotal' => $items->sum(fn ($line) => $line->quantity * $line->unit_price), 'discount_amount' => $items->sum('discount_amount'), 'tax_amount' => $items->sum('tax_amount'), 'total_amount' => $items->sum('total_amount'), 'balance_amount' => $items->sum('total_amount')]);
            DeliveryNote::where('created_by', creatorId())->where('sales_order_id', $order->id)->update(['invoice_id' => $invoice->id]);
            return $invoice;
        }, 3);
        return $request->expectsJson() ? response()->json($invoice) : redirect()->route('sales-invoices.show', $invoice)->with('success', __('Draft invoice created.'));
    }

    /**
     * Delete Sales Order
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $order = SalesOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($id);
            abort_if($order->items()->where('fulfilled_quantity','>',0)->exists() || DeliveryNote::where('sales_order_id',$id)->exists() || SalesInvoice::where('sales_order_id',$id)->exists(),409,'Orders with fulfillment or invoices must be retained.');
            $order->items()->delete(); $order->delete();
        },3);
        return back()->with('success', __('Unfulfilled order removed.'));
    }
}
