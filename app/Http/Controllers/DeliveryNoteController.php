<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DeliveryNoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\ProductService\Models\ProductServiceItem;

class DeliveryNoteController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-sales-invoices')];
    }
    /**
     * Display Delivery Notes & Dispatch Fulfillment Ledger (SRS Module 9).
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $creatorId = creatorId();
        $query = DeliveryNote::where('created_by', $creatorId)
            ->with(['items.product', 'invoice', 'customer', 'warehouse', 'vehicle', 'driver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('destination_city', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $deliveryNotes = $query->latest('delivery_date')->get();

        // Calculate KPI summary
        $totalDeliveries = DeliveryNote::where('created_by', $creatorId)->count();
        $inTransitDeliveries = DeliveryNote::where('created_by', $creatorId)->whereIn('status', ['dispatched', 'in_transit'])->count();
        $deliveredCount = DeliveryNote::where('created_by', $creatorId)->where('status', 'delivered')->count();
        $totalUnitsDispatched = (float) DeliveryNote::where('created_by', $creatorId)
            ->whereIn('status', ['dispatched', 'in_transit', 'delivered'])
            ->with('items')
            ->get()
            ->sum(fn($dn) => $dn->items->sum('quantity_shipped'));

        // Dropdown data for CUD modals
        $warehouses = Warehouse::where('created_by', $creatorId)->get(['id', 'name', 'address', 'city']);
        
        $customers = User::where(function ($q) use ($creatorId) {
            $q->where('created_by', $creatorId)->orWhere('id', $creatorId);
        })
        ->whereIn('type', ['client', 'customer', 'user', 'company'])
        ->get(['id', 'name', 'email', 'mobile_no']);

        $invoices = SalesInvoice::where('created_by', $creatorId)
            ->with(['items.product', 'customer'])
            ->latest('id')
            ->take(50)
            ->get(['id', 'invoice_number', 'customer_id', 'warehouse_id', 'total_amount', 'status']);

        $vehicles = Vehicle::where('created_by', $creatorId)
            ->get(['id', 'name', 'lincense_plate'])
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'plate_number' => $v->lincense_plate ?? $v->name,
                ];
            });

        $drivers = Driver::where('created_by', $creatorId)
            ->get(['id', 'name', 'phone']);

        $products = ProductServiceItem::where('created_by', $creatorId)
            ->where('is_active', true)
            ->get(['id', 'name', 'sku', 'sale_price', 'unit']);

        return Inertia::render('Sales/DeliveryNotes/Index', [
            'delivery_notes' => $deliveryNotes,
            'kpi' => [
                'total_deliveries' => $totalDeliveries,
                'in_transit_deliveries' => $inTransitDeliveries,
                'delivered_count' => $deliveredCount,
                'total_units_dispatched' => $totalUnitsDispatched,
            ],
            'filters' => $request->only(['status', 'warehouse_id', 'search']),
            'warehouses' => $warehouses,
            'customers' => $customers,
            'invoices' => $invoices,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'products' => $products,
        ]);
    }

    /**
     * Store (Create) a new Delivery Note (CUD: Create).
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date',
            'customer_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'driver_name' => 'nullable|string|max:150',
            'driver_phone' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:50',
            'shipping_address' => 'required|string|max:500',
            'destination_city' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'status' => 'nullable|in:draft,scheduled,dispatched',
            'total_packages' => 'nullable|integer|min:1',
            'total_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:200',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.quantity_shipped' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.batch_number' => 'nullable|string|max:100',
        ]);

        $creatorId = creatorId();
        $deliveryNote = DeliveryNoteService::createDeliveryNote($validated, $creatorId, Auth::id());

        return redirect()->back()->with('success', __('Delivery Note :num created successfully.', [
            'num' => $deliveryNote->delivery_number,
        ]));
    }

    /**
     * Update an existing Delivery Note (CUD: Update).
     */
    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        \App\Services\CompanyAccess::check($deliveryNote);
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date',
            'customer_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'driver_name' => 'nullable|string|max:150',
            'driver_phone' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:50',
            'shipping_address' => 'required|string|max:500',
            'destination_city' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'total_packages' => 'nullable|integer|min:1',
            'total_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:200',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.quantity_shipped' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.batch_number' => 'nullable|string|max:100',
        ]);

        DeliveryNoteService::updateDeliveryNote($deliveryNote, $validated);

        return redirect()->back()->with('success', __('Delivery Note :num updated successfully.', [
            'num' => $deliveryNote->delivery_number,
        ]));
    }

    /**
     * Dispatch Delivery Note & Decrement Warehouse Inventory (Action).
     */
    public function dispatchNote(DeliveryNote $deliveryNote)
    {
        \App\Services\CompanyAccess::check($deliveryNote);
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        if (in_array($deliveryNote->status, ['delivered', 'cancelled'])) {
            return back()->with('error', __('Cannot dispatch a :status delivery note.', ['status' => $deliveryNote->status]));
        }

        DeliveryNoteService::dispatchDeliveryNote($deliveryNote, Auth::id());

        return redirect()->back()->with('success', __('Delivery Note :num has been dispatched. Inventory stock decremented.', [
            'num' => $deliveryNote->delivery_number,
        ]));
    }

    /**
     * Record Customer Proof of Delivery (POD) (Action: Confirm).
     */
    public function confirmDelivery(Request $request, DeliveryNote $deliveryNote)
    {
        \App\Services\CompanyAccess::check($deliveryNote);
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'recipient_name' => 'required|string|max:150',
            'notes' => 'nullable|string|max:500',
            'actual_delivery_date' => 'nullable|date',
            'signature_token' => 'nullable|string|max:100',
        ]);

        DeliveryNoteService::confirmDelivery($deliveryNote, $validated, Auth::id());

        return redirect()->back()->with('success', __('Proof of Delivery (POD) confirmed for :num. Consignee: :name.', [
            'num' => $deliveryNote->delivery_number,
            'name' => $validated['recipient_name'],
        ]));
    }

    /**
     * Cancel Delivery Note & Restore Warehouse Inventory (Action).
     */
    public function cancelNote(DeliveryNote $deliveryNote)
    {
        \App\Services\CompanyAccess::check($deliveryNote);
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        if ($deliveryNote->status === 'cancelled') {
            return back()->with('error', __('Delivery note is already cancelled.'));
        }

        DeliveryNoteService::cancelDeliveryNote($deliveryNote, Auth::id());

        return redirect()->back()->with('success', __('Delivery Note :num cancelled. Inventory stock restored.', [
            'num' => $deliveryNote->delivery_number,
        ]));
    }

    /**
     * Delete Delivery Note (CUD: Delete).
     */
    public function destroy(DeliveryNote $deliveryNote)
    {
        \App\Services\CompanyAccess::check($deliveryNote);
        if (!Auth::user()->can('manage-sales-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $num = $deliveryNote->delivery_number;
        DeliveryNoteService::deleteDeliveryNote($deliveryNote);

        return redirect()->back()->with('success', __('Delivery Note :num deleted successfully.', [
            'num' => $num,
        ]));
    }
}
