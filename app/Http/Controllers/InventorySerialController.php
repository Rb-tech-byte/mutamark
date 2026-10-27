<?php

namespace App\Http\Controllers;

use App\Models\InventorySerialNumber;
use App\Models\Warehouse;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InventorySerialController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-inventory-serials')) {
            return back()->with('error', __('Permission denied'));
        }

        $userId = creatorId();

        $query = InventorySerialNumber::with([
            'product:id,name,sku,sale_price,purchase_price',
            'warehouse:id,name,city'
        ])->where('created_by', $userId);

        // Filter by Search (serial or batch number)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pQuery) use ($search) {
                      $pQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        // Filter by Warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $serials = $query->latest('id')->paginate($request->get('per_page', 10))->withQueryString();

        // Calculate KPI Metrics
        $totalUnits = InventorySerialNumber::where('created_by', $userId)->count();
        $inStockUnits = InventorySerialNumber::where('created_by', $userId)->where('status', 'in_stock')->count();
        $dispatchedUnits = InventorySerialNumber::where('created_by', $userId)->whereIn('status', ['allocated', 'dispatched', 'delivered'])->count();
        $defectiveUnits = InventorySerialNumber::where('created_by', $userId)->whereIn('status', ['defective', 'under_maintenance'])->count();
        $totalStockValue = (float) InventorySerialNumber::where('created_by', $userId)->where('status', 'in_stock')->sum('purchase_cost');

        $warehouses = Warehouse::where('created_by', $userId)->where('is_active', true)->get(['id', 'name', 'city']);
        $products = ProductServiceItem::where('created_by', $userId)->where('type', '!=', 'service')->get(['id', 'name', 'sku', 'purchase_price', 'sale_price']);

        return Inertia::render('Inventory/Serials/Index', [
            'serials' => $serials,
            'warehouses' => $warehouses,
            'products' => $products,
            'stats' => [
                'total_units' => $totalUnits,
                'in_stock_units' => $inStockUnits,
                'dispatched_units' => $dispatchedUnits,
                'defective_units' => $defectiveUnits,
                'total_stock_value' => $totalStockValue,
            ],
            'filters' => [
                'search' => $request->get('search', ''),
                'product_id' => $request->get('product_id', ''),
                'warehouse_id' => $request->get('warehouse_id', ''),
                'status' => $request->get('status', ''),
            ]
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('create-inventory-serials')) {
            return back()->with('error', __('Permission denied'));
        }

        $userId = creatorId();

        $validated = $request->validate([
            'serial_number' => 'required|string|max:191',
            'batch_number' => 'nullable|string|max:191',
            'product_id' => 'required|exists:product_service_items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'status' => 'required|in:in_stock,allocated,dispatched,delivered,defective,under_maintenance',
            'purchase_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'barcode' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
        ]);

        // Check if serial already exists for this company
        $exists = InventorySerialNumber::where('created_by', $userId)
            ->where('serial_number', $validated['serial_number'])
            ->exists();

        if ($exists) {
            return back()->with('error', __('A hardware item with this Serial Number already exists.'));
        }

        $serial = new InventorySerialNumber();
        $serial->serial_number = $validated['serial_number'];
        $serial->batch_number = $validated['batch_number'] ?? null;
        $serial->product_id = $validated['product_id'];
        $serial->warehouse_id = $validated['warehouse_id'];
        $serial->status = $validated['status'];
        $serial->purchase_cost = $validated['purchase_cost'] ?? 0;
        $serial->selling_price = $validated['selling_price'] ?? 0;
        $serial->warranty_months = $validated['warranty_months'] ?? 12;
        $serial->warranty_expiry_date = $validated['warranty_expiry_date'] ?? null;
        $serial->barcode = $validated['barcode'] ?? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validated['serial_number']));
        $serial->notes = $validated['notes'] ?? null;
        $serial->creator_id = Auth::id();
        $serial->created_by = $userId;
        $serial->save();

        // If in_stock, sync warehouse stock
        if ($serial->status === 'in_stock') {
            $stock = WarehouseStock::firstOrCreate(
                ['product_id' => $serial->product_id, 'warehouse_id' => $serial->warehouse_id],
                ['quantity' => 0]
            );
            $stock->quantity += 1;
            $stock->save();
        }

        return redirect()->back()->with('success', __('Serial number registered successfully.'));
    }

    public function update(Request $request, InventorySerialNumber $serial)
    {
        if (!Auth::user()->can('edit-inventory-serials')) {
            return back()->with('error', __('Permission denied'));
        }

        $validated = $request->validate([
            'serial_number' => 'required|string|max:191',
            'batch_number' => 'nullable|string|max:191',
            'product_id' => 'required|exists:product_service_items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'status' => 'required|in:in_stock,allocated,dispatched,delivered,defective,under_maintenance',
            'purchase_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'barcode' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $serial->status;
        $oldWarehouse = $serial->warehouse_id;

        $serial->update($validated);

        // Adjust warehouse stock if status changed
        if ($oldStatus === 'in_stock' && $serial->status !== 'in_stock') {
            $stock = WarehouseStock::where('product_id', $serial->product_id)->where('warehouse_id', $oldWarehouse)->first();
            if ($stock) {
                $stock->quantity = max(0, $stock->quantity - 1);
                $stock->save();
            }
        } elseif ($oldStatus !== 'in_stock' && $serial->status === 'in_stock') {
            $stock = WarehouseStock::firstOrCreate(
                ['product_id' => $serial->product_id, 'warehouse_id' => $serial->warehouse_id],
                ['quantity' => 0]
            );
            $stock->quantity += 1;
            $stock->save();
        }

        return redirect()->back()->with('success', __('Serial number updated successfully.'));
    }

    public function destroy(InventorySerialNumber $serial)
    {
        if (!Auth::user()->can('delete-inventory-serials')) {
            return back()->with('error', __('Permission denied'));
        }

        if ($serial->status === 'in_stock') {
            $stock = WarehouseStock::where('product_id', $serial->product_id)->where('warehouse_id', $serial->warehouse_id)->first();
            if ($stock) {
                $stock->quantity = max(0, $stock->quantity - 1);
                $stock->save();
            }
        }

        $serial->delete();

        return redirect()->back()->with('success', __('Serial item removed successfully.'));
    }

    /**
     * API lookup by Serial Number / Barcode for scanners
     */
    public function apiShow($serialNumber)
    {
        $serial = InventorySerialNumber::with([
            'product:id,name,sku,sale_price',
            'warehouse:id,name,city'
        ])
        ->where('serial_number', $serialNumber)
        ->orWhere('barcode', $serialNumber)
        ->first();

        if (!$serial) {
            return response()->json([
                'success' => false,
                'message' => 'Hardware item with serial/barcode ' . $serialNumber . ' not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $serial->id,
                'serial_number' => $serial->serial_number,
                'batch_number' => $serial->batch_number,
                'barcode' => $serial->barcode,
                'status' => $serial->status,
                'product_name' => $serial->product?->name,
                'product_sku' => $serial->product?->sku,
                'warehouse' => $serial->warehouse?->name,
                'warehouse_city' => $serial->warehouse?->city,
                'warranty_expiry_date' => $serial->warranty_expiry_date?->format('Y-m-d'),
                'warranty_valid' => $serial->warranty_expiry_date ? $serial->warranty_expiry_date->isFuture() : true,
                'selling_price' => (float)$serial->selling_price,
            ]
        ]);
    }
}
