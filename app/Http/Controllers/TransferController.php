<?php

namespace App\Http\Controllers;

use App\Models\FinancialAuditLog;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;
use App\Http\Requests\StoreTransferRequest;
use App\Events\CreateTransfer;
use App\Events\DestroyTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyAccess;
use Inertia\Inertia;

class TransferController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-transfers')) {
            $creatorId = creatorId();

            $transfers = Transfer::query()
                ->with([
                    'fromWarehouse:id,name,code,city',
                    'toWarehouse:id,name,code,city',
                    'product:id,name,sku',
                    'items.product:id,name,sku,purchase_price',
                    'receiver:id,name',
                    'verification',
                ])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-transfers')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-transfers')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('product_name'), function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereHas('product', function ($query) {
                            $query->where('name', 'like', '%' . request('product_name') . '%');
                        })->orWhereHas('items.product', function ($query) {
                            $query->where('name', 'like', '%' . request('product_name') . '%');
                        });
                    });
                })
                ->when(request('from_warehouse'), fn($q) => $q->where('from_warehouse', request('from_warehouse')))
                ->when(request('to_warehouse'), fn($q) => $q->where('to_warehouse', request('to_warehouse')))
                ->when(request('status'), fn($q) => $q->where('status', request('status')))
                ->when(request('search'), function ($q) {
                    $search = request('search');
                    $q->where(function ($sub) use ($search) {
                        $sub->where('transfer_number', 'like', "%{$search}%")
                            ->orWhere('driver_name', 'like', "%{$search}%")
                            ->orWhere('vehicle_number', 'like', "%{$search}%");
                    });
                })
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $warehouses = Warehouse::where('created_by', $creatorId)
                ->where('is_active', true)
                ->with(['locations:id,warehouse_id,zone,bin_code'])
                ->get(['id', 'name', 'code', 'city', 'warehouse_type']);

            $products = ProductServiceItem::where('created_by', $creatorId)->get(['id', 'name', 'sku', 'purchase_price']);

            $warehouseStocks = WarehouseStock::with('product:id,name,sku,purchase_price')
                ->whereHas('product', function ($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })
                ->where('quantity', '>', 0)
                ->get();

            // Calculate WMS Statistics
            $baseQuery = Transfer::where('created_by', $creatorId);
            $statistics = [
                'total_transfers' => (clone $baseQuery)->count(),
                'in_transit' => (clone $baseQuery)->where('status', 'in_transit')->count(),
                'received' => (clone $baseQuery)->whereIn('status', ['received', 'completed'])->count(),
                'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
                'total_quantity_moved' => (clone $baseQuery)->sum('total_transferred_qty'),
                'active_warehouses_count' => $warehouses->count(),
            ];

            return Inertia::render('Transfers/Index', [
                'transfers' => $transfers,
                'warehouses' => $warehouses,
                'products' => $products,
                'warehouseStocks' => $warehouseStocks,
                'statistics' => $statistics,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        return back();
    }

    public function store(StoreTransferRequest $request)
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['transfers', 'transfer_items', 'warehouse_stocks', 'document_verifications']);
        if (Auth::user()->can('create-transfers')) {
            $validated = $request->validated();
            $creatorId = creatorId();
            $itemsData = $request->input('items', []);
            return DB::transaction(function () use ($request, $validated, $creatorId, $itemsData) {

            $transfer = new Transfer();
            $transfer->from_warehouse = $validated['from_warehouse'];
            $transfer->to_warehouse = $validated['to_warehouse'];
            $transfer->date = $validated['date'];
            $transfer->expected_delivery_date = $validated['expected_delivery_date'] ?? null;
            $transfer->driver_name = $validated['driver_name'] ?? null;
            $transfer->vehicle_number = $validated['vehicle_number'] ?? null;
            $transfer->shipping_carrier = $validated['shipping_carrier'] ?? null;
            $transfer->transit_notes = $validated['transit_notes'] ?? null;
            $initialStatus = $validated['initial_status'] ?? 'in_transit';
            $transfer->status = 'draft';
            $transfer->creator_id = Auth::id();
            $transfer->created_by = $creatorId;

            // Handle multi-line vs single item
            if (!empty($itemsData) && is_array($itemsData)) {
                $totalQty = 0;
                $firstProduct = $itemsData[0];
                $transfer->product_id = $firstProduct['product_id'];
                $transfer->quantity = $firstProduct['quantity'];
                $transfer->total_items_count = count($itemsData);

                foreach ($itemsData as $it) {
                    $totalQty += (float)$it['quantity'];
                }
                $transfer->total_transferred_qty = $totalQty;
                $transfer->save();

                // Save line items
                foreach ($itemsData as $it) {
                    $prod = ProductServiceItem::find($it['product_id']);
                    TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'product_id' => $it['product_id'],
                        'requested_quantity' => $it['quantity'],
                        'dispatched_quantity' => $it['quantity'],
                        'received_quantity' => $transfer->status === 'completed' ? $it['quantity'] : null,
                        'from_bin_id' => $it['from_bin_id'] ?? null,
                        'to_bin_id' => $it['to_bin_id'] ?? null,
                        'unit_cost' => $prod?->purchase_price ?: 0,
                        'notes' => $it['notes'] ?? null,
                    ]);
                }
            } else {
                // Backward compatible single item
                $transfer->product_id = $validated['product_id'];
                $transfer->quantity = $validated['quantity'];
                $transfer->total_items_count = 1;
                $transfer->total_transferred_qty = $validated['quantity'];
                $transfer->save();

                $prod = ProductServiceItem::find($validated['product_id']);
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $validated['product_id'],
                    'requested_quantity' => $validated['quantity'],
                    'dispatched_quantity' => $validated['quantity'],
                    'received_quantity' => $transfer->status === 'completed' ? $validated['quantity'] : null,
                    'unit_cost' => $prod?->purchase_price ?: 0,
                ]);
            }

            // If created as in_transit or completed, adjust stock accordingly
            if ($initialStatus === 'in_transit') {
                $transfer->dispatchOrder();
            } elseif ($initialStatus === 'completed') {
                // Immediate transfer (old behavior)
                $transfer->dispatchOrder();
                $transfer->receiveOrder([], null, Auth::id());
            }

            CreateTransfer::dispatch($request, $transfer);

            return back()->with('success', __("Stock Transfer Order :number created successfully.", ['number' => $transfer->transfer_number]));
            }, 3);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(Transfer $transfer)
    {
        CompanyAccess::check($transfer);
        if (Auth::user()->can('manage-transfers')) {
            $transfer->load([
                'fromWarehouse.locations',
                'toWarehouse.locations',
                'product',
                'items.product',
                'items.fromBin',
                'items.toBin',
                'receiver',
                'creator',
                'verification',
            ]);

            return Inertia::render('Transfers/Show', [
                'transfer' => $transfer,
            ]);
        }
        return back()->with('error', __('Permission denied'));
    }

    /**
     * Dispatch an existing draft transfer order
     */
    public function dispatchTransfer(Request $request, Transfer $transfer)
    {
        CompanyAccess::check($transfer);
        if (!Auth::user()->can('manage-transfers')) {
            return back()->with('error', __('Permission denied'));
        }

        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:191',
            'shipping_carrier' => 'nullable|string|max:191',
            'transit_notes' => 'nullable|string',
        ]);

        $dispatched = $transfer->dispatchOrder($validated);

        if (!$dispatched) {
            return back()->with('error', __('Transfer cannot be dispatched in its current status.'));
        }

        return back()->with('success', __("Transfer order :number has been dispatched and is now in transit.", ['number' => $transfer->transfer_number]));
    }

    /**
     * Receive and inspect a transfer order at destination warehouse
     */
    public function receiveTransfer(Request $request, Transfer $transfer)
    {
        CompanyAccess::check($transfer);
        if (!Auth::user()->can('manage-transfers')) {
            return back()->with('error', __('Permission denied'));
        }

        $validated = $request->validate([
            'inspections' => 'nullable|array',
            'inspections.*' => 'numeric|min:0',
            'inspection_notes' => 'nullable|string',
        ]);

        $received = $transfer->receiveOrder(
            $validated['inspections'] ?? [],
            $validated['inspection_notes'] ?? null,
            Auth::id()
        );

        if (!$received) {
            return back()->with('error', __('Transfer cannot be received in its current status.'));
        }

        return back()->with('success', __("Transfer order :number successfully received and inspected at :warehouse.", [
            'number' => $transfer->transfer_number,
            'warehouse' => $transfer->toWarehouse?->name,
        ]));
    }

    /**
     * Printable Waybill Screen
     */
    public function waybill(Transfer $transfer)
    {
        CompanyAccess::check($transfer);
        if (!Auth::user()->can('manage-transfers')) {
            return back()->with('error', __('Permission denied'));
        }

        $transfer->load([
            'fromWarehouse',
            'toWarehouse',
            'product',
            'items.product',
            'receiver',
            'creator',
            'verification',
        ]);

        return Inertia::render('Transfers/Waybill', [
            'transfer' => $transfer,
        ]);
    }

    public function destroy(Transfer $transfer)
    {
        CompanyAccess::requirePermission('delete-transfers');
        CompanyAccess::check($transfer);
        \App\Services\TransactionSafety::requireTransactionalTables(['transfers', 'transfer_items']);
        DB::transaction(function () use ($transfer) {
            $locked = Transfer::where('created_by', creatorId())->lockForUpdate()->findOrFail($transfer->id);
            abort_unless($locked->status === 'draft', 409, 'Dispatched transfers require an authorized reversal, not deletion.');
            DestroyTransfer::dispatch($locked);
            $locked->items()->delete();
            $locked->delete();
        }, 3);
        return back()->with('success', __('The transfer has been deleted.'));
    }
}
