<?php

namespace App\Http\Controllers;

use App\Models\DocumentVerification;
use App\Models\FinancialAuditLog;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\CompanyAccess;
use Inertia\Inertia;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['per_page' => 'sometimes|integer|min:1|max:100']);
        if (!Auth::user()->can('manage-stock')) {
            abort(403, __('Permission denied.'));
        }

        $creatorId = creatorId();

        $adjustments = StockAdjustment::query()
            ->with(['warehouse:id,name,code,city', 'items.product:id,name,sku', 'approver:id,name'])
            ->where('created_by', $creatorId)
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->adjustment_type, fn($q) => $q->where('adjustment_type', $request->adjustment_type))
            ->when($request->search, fn($q) => $q->where('adjustment_number', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($request->per_page ?: 10)
            ->withQueryString();
        if ($request->expectsJson()) return response()->json($adjustments);

        $warehouses = Warehouse::where('created_by', $creatorId)
            ->where('is_active', true)
            ->get(['id', 'name', 'code', 'city']);

        $products = ProductServiceItem::where('created_by', $creatorId)
            ->get(['id', 'name', 'sku', 'purchase_price']);

        $warehouseStocks = WarehouseStock::with('product:id,name,sku,purchase_price')
            ->whereHas('product', fn($q) => $q->where('created_by', $creatorId))
            ->get();

        $statistics = [
            'total_adjustments' => StockAdjustment::where('created_by', $creatorId)->count(),
            'total_variance_value' => (float)StockAdjustment::where('created_by', $creatorId)->sum('total_variance_value'),
            'approved_count' => StockAdjustment::where('created_by', $creatorId)->whereIn('status', ['approved', 'posted'])->count(),
            'pending_count' => StockAdjustment::where('created_by', $creatorId)->where('status', 'draft')->count(),
        ];

        return Inertia::render('StockAdjustments/Index', [
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
            'products' => $products,
            'warehouseStocks' => $warehouseStocks,
            'statistics' => $statistics,
        ]);
    }

    public function store(Request $request)
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['stock_adjustments', 'stock_adjustment_items', 'warehouse_stocks']);
        CompanyAccess::requirePermission('manage-stock');
        if (!Auth::user()->can('manage-stock')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('created_by', creatorId())],
            'adjustment_type' => 'required|in:cycle_count,damage_spoilage,theft_loss,revaluation,opening_balance',
            'adjustment_date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'distinct', Rule::exists('product_service_items', 'id')->where('created_by', creatorId())],
            'items.*.counted_quantity' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        $creatorId = creatorId();
        $adjustment = DB::transaction(function () use ($validated, $creatorId) {
        $totalVarianceValue = 0;

        $adjustment = new StockAdjustment();
        $adjustment->warehouse_id = $validated['warehouse_id'];
        $adjustment->adjustment_type = $validated['adjustment_type'];
        $adjustment->adjustment_date = $validated['adjustment_date'];
        $adjustment->reason = $validated['reason'];
        $adjustment->status = 'draft';
        $adjustment->creator_id = Auth::id();
        $adjustment->created_by = $creatorId;
        $adjustment->save();

        foreach ($validated['items'] as $itemData) {
            $stock = WarehouseStock::where('warehouse_id', $validated['warehouse_id'])
                ->where('product_id', $itemData['product_id'])
                ->first();

            $prod = ProductServiceItem::find($itemData['product_id']);
            $sysQty = $stock ? (float)$stock->quantity : 0;
            $cntQty = (float)$itemData['counted_quantity'];
            $diff = $cntQty - $sysQty;
            $cost = (float)($prod?->purchase_price ?: 0);
            $itemVariance = $diff * $cost;

            StockAdjustmentItem::create([
                'adjustment_id' => $adjustment->id,
                'product_id' => $itemData['product_id'],
                'system_quantity' => $sysQty,
                'counted_quantity' => $cntQty,
                'difference_quantity' => $diff,
                'unit_cost' => $cost,
                'variance_value' => $itemVariance,
                'remarks' => $itemData['remarks'] ?? null,
            ]);

            $totalVarianceValue += $itemVariance;
        }

        $adjustment->total_variance_value = $totalVarianceValue;
        $adjustment->save();
        return $adjustment;
        });
        return $request->expectsJson() ? response()->json($adjustment->load('items'), 201) : back()->with('success', __("Stock Adjustment Order :number created successfully.", ['number' => $adjustment->adjustment_number]));
    }

    public function postAdjustment(Request $request, StockAdjustment $stockAdjustment)
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['stock_adjustments', 'warehouse_stocks', 'document_verifications']);
        CompanyAccess::requirePermission('manage-stock');
        if (!Auth::user()->can('manage-stock')) {
            return back()->with('error', __('Permission denied.'));
        }

        CompanyAccess::check($stockAdjustment);
        DB::transaction(function () use ($stockAdjustment) {
        $stockAdjustment = StockAdjustment::lockForUpdate()->findOrFail($stockAdjustment->id);
        if ($stockAdjustment->status === 'posted') return;

        // Apply item differences to warehouse stocks
        foreach ($stockAdjustment->items as $item) {
            $stock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $stockAdjustment->warehouse_id,
                    'product_id' => $item->product_id,
                ],
                ['quantity' => 0]
            );
            $stock = WarehouseStock::lockForUpdate()->findOrFail($stock->id);
            if (abs((float) $stock->quantity - (float) $item->system_quantity) > 0.00001) {
                throw ValidationException::withMessages(['stock' => __('Stock changed after this count. Record a fresh count before posting.')]);
            }

            $stock->quantity = $item->counted_quantity;
            $stock->save();
        }

        $stockAdjustment->status = 'posted';
        $stockAdjustment->approved_by = Auth::id();
        $stockAdjustment->posted_at = now();
        $stockAdjustment->save();

        $value = round((float) $stockAdjustment->items->sum('variance_value'),2);
        if (abs($value) > 0.01) {
            $inventory = \Workdo\Account\Models\ChartOfAccount::where('created_by',creatorId())->where('account_code','1200')->firstOrFail();
            $adjustmentAccount = company_setting('inventory_adjustment_account_id');
            abort_unless($adjustmentAccount,422,'Configure the inventory adjustment GL account in accounting controls.');
            $amount = abs($value);
            app(\Workdo\Account\Services\JournalService::class)->postControlledJournal('stock_adjustment',$stockAdjustment->id,(string) $stockAdjustment->adjustment_date,[
                ['account_id'=>$inventory->id,'debit_amount'=>$value > 0 ? $amount : 0,'credit_amount'=>$value < 0 ? $amount : 0],
                ['account_id'=>$adjustmentAccount,'debit_amount'=>$value < 0 ? $amount : 0,'credit_amount'=>$value > 0 ? $amount : 0],
            ]);
        }

        // Create Cryptographic Document Seal
        $payload = json_encode([
            'document_type' => 'stock_adjustment',
            'adjustment_number' => $stockAdjustment->adjustment_number,
            'warehouse_id' => $stockAdjustment->warehouse_id,
            'total_variance_value' => $stockAdjustment->total_variance_value,
            'posted_at' => $stockAdjustment->posted_at->toIso8601String(),
        ]);
        $hash = hash('sha256', $payload . config('app.key'));
        DocumentVerification::updateOrCreate(
            [
                'document_type' => 'stock_adjustment',
                'document_id' => $stockAdjustment->id,
            ],
            [
                'document_number' => $stockAdjustment->adjustment_number,
                'verification_hash' => $hash,
                'qr_payload' => url("/verify/document/{$hash}"),
                'created_by' => $stockAdjustment->created_by,
            ]
        );
        });

        return $request->expectsJson() ? response()->json($stockAdjustment->fresh()->load('items')) : back()->with('success', __("Stock Adjustment :number has been approved and inventory reconciled.", ['number' => $stockAdjustment->adjustment_number]));
    }

    public function scan(Request $request)
    {
        CompanyAccess::requirePermission('manage-stock');
        $data = $request->validate(['code' => 'required|string|max:191', 'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('created_by', creatorId())]]);
        $product = ProductServiceItem::where('created_by', creatorId())->where('sku', $data['code'])->first();
        $serial = null;
        if (!$product) {
            $serial = \App\Models\InventorySerialNumber::forCompany()->inStock()->where('warehouse_id', $data['warehouse_id'])
                ->where(fn ($q) => $q->where('serial_number', $data['code'])->orWhere('barcode', $data['code']))->first();
            $product = $serial?->product;
        }
        abort_unless($product && (int) $product->created_by === (int) creatorId(), 404, 'Product not found.');
        $quantity = WarehouseStock::where('warehouse_id', $data['warehouse_id'])->where('product_id', $product->id)->sum('quantity');
        return response()->json(['product_id' => $product->id, 'name' => $product->name, 'system_quantity' => (float) $quantity, 'serial_id'=>$serial?->id]);
    }
}
