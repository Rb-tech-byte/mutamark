<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PurchaseRequisitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\ProductService\Models\ProductServiceItem;

class PurchaseRequisitionController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array { return [new \Illuminate\Routing\Controllers\Middleware('can:manage-purchase-invoices')]; }
    /**
     * Display Purchase Requisitions & Material Requests Ledger (SRS Module 8).
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $creatorId = creatorId();
        $query = PurchaseRequisition::where('created_by', $creatorId)
            ->with(['items.product', 'requester', 'warehouse', 'preferredVendor', 'approver', 'convertedInvoice']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department_name', $request->department);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('requisition_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($rq) use ($search) {
                        $rq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        $requisitions = $query->latest('requisition_date')->get();

        // Calculate KPI metrics
        $allReqs = PurchaseRequisition::where('created_by', $creatorId)->get();
        $totalRequisitions = $allReqs->count();
        $pendingApprovalCount = $allReqs->where('status', 'submitted')->count();
        $approvedCount = $allReqs->where('status', 'approved')->count();
        $convertedCount = $allReqs->whereIn('status', ['converted_to_po', 'converted_to_rfq', 'converted'])->count();
        $totalValueTzs = (float) $allReqs->sum('estimated_total_cost');

        // Dropdown data
        $warehouses = Warehouse::where('created_by', $creatorId)->get(['id', 'name', 'address', 'city']);
        $vendors = User::where('type', 'vendor')
            ->where('created_by', $creatorId)
            ->get(['id', 'name', 'email', 'mobile_no']);

        $staffUsers = User::where(function ($q) use ($creatorId) {
            $q->where('created_by', $creatorId)->orWhere('id', $creatorId);
        })
        ->get(['id', 'name', 'email']);

        $products = ProductServiceItem::where('created_by', $creatorId)
            ->where('is_active', true)
            ->get(['id', 'name', 'sku', 'purchase_price', 'sale_price', 'unit']);

        $departments = [
            'Operations',
            'Engineering',
            'IT & Technology',
            'Finance & Accounts',
            'Logistics & Fleet',
            'Warehouse & Supply Chain',
            'Administration & HR',
            'Quality Control',
        ];

        return Inertia::render('Procurement/PurchaseRequisitions/Index', [
            'requisitions' => $requisitions,
            'kpi' => [
                'total_requisitions' => $totalRequisitions,
                'pending_approval_count' => $pendingApprovalCount,
                'approved_count' => $approvedCount,
                'converted_count' => $convertedCount,
                'total_value_tzs' => $totalValueTzs,
            ],
            'filters' => $request->only(['status', 'department', 'priority', 'search']),
            'warehouses' => $warehouses,
            'vendors' => $vendors,
            'staff_users' => $staffUsers,
            'products' => $products,
            'departments' => $departments,
        ]);
    }

    /**
     * Store (Create) a new Purchase Requisition (CUD: Create).
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'requisition_date' => 'required|date',
            'required_by_date' => 'nullable|date',
            'requester_id' => 'required|exists:users,id',
            'department' => 'required|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'preferred_vendor_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'nullable|in:draft,submitted',
            'justification' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:200',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
            'items.*.estimated_unit_cost' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.specifications' => 'nullable|string|max:500',
        ]);

        $creatorId = creatorId();
        $requisition = PurchaseRequisitionService::createRequisition($validated, $creatorId, Auth::id());

        return redirect()->back()->with('success', __('Purchase Requisition :num created successfully.', [
            'num' => $requisition->requisition_number,
        ]));
    }

    /**
     * Update an existing Purchase Requisition (CUD: Update).
     */
    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        \App\Services\CompanyAccess::check($purchaseRequisition);
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        if (in_array($purchaseRequisition->status, ['approved', 'converted'])) {
            return back()->with('error', __('Cannot edit an already approved or converted requisition.'));
        }

        $validated = $request->validate([
            'requisition_date' => 'required|date',
            'required_by_date' => 'nullable|date',
            'requester_id' => 'required|exists:users,id',
            'department' => 'required|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'preferred_vendor_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'justification' => 'required|string|max:1000',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:200',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
            'items.*.estimated_unit_cost' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.specifications' => 'nullable|string|max:500',
        ]);

        PurchaseRequisitionService::updateRequisition($purchaseRequisition, $validated);

        return redirect()->back()->with('success', __('Purchase Requisition :num updated successfully.', [
            'num' => $purchaseRequisition->requisition_number,
        ]));
    }

    /**
     * Submit Purchase Requisition for Department Approval.
     */
    public function submit(PurchaseRequisition $purchaseRequisition)
    {
        \App\Services\CompanyAccess::check($purchaseRequisition);
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        PurchaseRequisitionService::submitRequisition($purchaseRequisition);

        return redirect()->back()->with('success', __('Purchase Requisition :num submitted for management approval.', [
            'num' => $purchaseRequisition->requisition_number,
        ]));
    }

    /**
     * Approve Purchase Requisition (Action).
     */
    public function approve(PurchaseRequisition $purchaseRequisition)
    {
        \App\Services\CompanyAccess::check($purchaseRequisition);
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        PurchaseRequisitionService::approveRequisition($purchaseRequisition, Auth::id());

        return redirect()->back()->with('success', __('Purchase Requisition :num approved successfully.', [
            'num' => $purchaseRequisition->requisition_number,
        ]));
    }

    /**
     * Reject Purchase Requisition (Action).
     */
    public function reject(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        \App\Services\CompanyAccess::check($purchaseRequisition);
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        PurchaseRequisitionService::rejectRequisition($purchaseRequisition, $validated['rejection_reason'], Auth::id());

        return redirect()->back()->with('success', __('Purchase Requisition :num has been rejected.', [
            'num' => $purchaseRequisition->requisition_number,
        ]));
    }

    /**
     * Convert Approved Purchase Requisition to Purchase Bill / Order (PurchaseInvoice).
     */
    public function convertToInvoice(PurchaseRequisition $purchaseRequisition)
    {
        \App\Services\CompanyAccess::check($purchaseRequisition);
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        if ($purchaseRequisition->status !== 'approved') {
            return back()->with('error', __('Only approved requisitions can be converted into purchase orders.'));
        }

        $result = PurchaseRequisitionService::convertToPurchaseInvoice($purchaseRequisition, Auth::id());

        return redirect()->back()->with('success', __('Requisition :pr converted successfully into Purchase Order :inv.', [
            'pr' => $purchaseRequisition->requisition_number,
            'inv' => $result['purchase_order']->po_number,
        ]));
    }

    /**
     * Delete Purchase Requisition (CUD: Delete).
     */
    public function destroy(PurchaseRequisition $purchaseRequisition)
    {
        \App\Services\CompanyAccess::check($purchaseRequisition);
        if (!Auth::user()->can('manage-purchase-invoices')) {
            return back()->with('error', __('Permission denied.'));
        }

        if (in_array($purchaseRequisition->status, ['converted', 'converted_to_po', 'converted_to_rfq'])) {
            return back()->with('error', __('Cannot delete a converted requisition.'));
        }

        $num = $purchaseRequisition->requisition_number;
        PurchaseRequisitionService::deleteRequisition($purchaseRequisition);

        return redirect()->back()->with('success', __('Purchase Requisition :num deleted successfully.', [
            'num' => $num,
        ]));
    }
}
