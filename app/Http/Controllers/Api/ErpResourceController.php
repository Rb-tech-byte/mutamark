<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanyAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ErpResourceController extends Controller
{
    use ApiResponseTrait;

    /** Read contracts reuse the existing domain models; writes use their domain controllers/services. */
    private function resources(): array
    {
        return [
            'purchase-orders' => [\App\Models\PurchaseOrder::class, 'manage-purchase-invoices', ['id','po_number','vendor_id','warehouse_id','status','total_amount','currency']],
            'customers' => [\Workdo\Account\Models\Customer::class, 'manage-customers', ['id', 'user_id', 'customer_code', 'company_name', 'contact_person_name', 'payment_terms']],
            'suppliers' => [\Workdo\Account\Models\Vendor::class, 'manage-vendors', ['id', 'user_id', 'vendor_code', 'company_name', 'contact_person_name', 'payment_terms', 'is_active']],
            'products' => [\Workdo\ProductService\Models\ProductServiceItem::class, 'manage-product-service-item', ['id', 'name', 'sku', 'type', 'sale_price', 'purchase_price', 'unit', 'is_active']],
            'inventory' => [\Workdo\ProductService\Models\WarehouseStock::class, 'manage-stock', ['id', 'warehouse_id', 'product_id', 'quantity']],
            'sales-orders' => [\App\Models\SalesOrder::class, 'manage-sales-invoices', ['id', 'order_number', 'customer_id', 'status', 'total_amount']],
            'sales-invoices' => [\App\Models\SalesInvoice::class, 'manage-sales-invoices', ['id', 'invoice_number', 'customer_id', 'warehouse_id', 'invoice_date', 'due_date', 'status', 'total_amount', 'paid_amount', 'balance_amount']],
            'purchase-invoices' => [\App\Models\PurchaseInvoice::class, 'manage-purchase-invoices', ['id', 'invoice_number', 'vendor_id', 'invoice_date', 'due_date', 'status', 'total_amount', 'paid_amount', 'balance_amount']],
            'payments' => [\App\Models\PaymentTransaction::class, 'manage-customer-payments', ['id', 'transaction_reference', 'invoice_id', 'invoice_number', 'gateway_key', 'amount', 'currency', 'status', 'paid_at']],
            'accounting' => [\Workdo\Account\Models\JournalEntry::class, 'manage-journal-entries', ['id', 'journal_number', 'journal_date', 'description', 'total_debit', 'total_credit', 'status']],
            'employees' => [\Workdo\Hrm\Models\Employee::class, 'manage-employees', ['id', 'user_id', 'employee_id', 'branch_id', 'department_id']],
        ];
    }

    private function query(string $resource)
    {
        abort_unless(isset($this->resources()[$resource]), 404);
        [$model, $permission, $columns] = $this->resources()[$resource];
        CompanyAccess::requirePermission($permission);
        $query = $model::query()->select($columns);
        if ($resource === 'inventory') {
            $query->whereHas('product', fn ($q) => $q->where('created_by', creatorId()))
                ->whereIn('warehouse_id', \App\Models\Warehouse::where('created_by', creatorId())->select('id'));
        } else {
            $query->where($resource === 'payments' ? 'creator_id' : 'created_by', creatorId());
        }
        // Honour the existing own/any distinction instead of broadening read access.
        $ownPermissions = [
            'customers' => ['manage-any-customers', 'manage-own-customers'],
            'suppliers' => ['manage-any-vendors', 'manage-own-vendors'],
            'employees' => ['manage-any-employees', 'manage-own-employees'],
            'sales-invoices' => ['manage-any-sales-invoices', 'manage-own-sales-invoices'],
            'purchase-invoices' => ['manage-any-purchase-invoices', 'manage-own-purchase-invoices'],
            'payments' => ['manage-any-customer-payments', 'manage-own-customer-payments', 'created_by'],
        ];
        if (isset($ownPermissions[$resource])) {
            [$any, $own] = $ownPermissions[$resource];
            if (!auth()->user()->can($any)) {
                $query->where(auth()->user()->can($own) ? ($ownPermissions[$resource][2] ?? 'creator_id') : 'id', auth()->user()->can($own) ? auth()->id() : -1);
            }
        }
        return $query;
    }

    public function index(Request $request, string $resource)
    {
        $request->validate(['per_page' => 'sometimes|integer|min:1|max:100', 'page' => 'sometimes|integer|min:1']);
        return $this->paginatedResponse($this->query($resource)->orderByDesc('id')->paginate($request->integer('per_page', 20)));
    }

    public function show(string $resource, int $id)
    {
        return $this->successResponse($this->query($resource)->findOrFail($id));
    }
}
