<?php

namespace App\Http\Controllers;

use App\Models\ProformaInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CompanyAccess;
use App\Services\ProformaInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Workdo\ProductService\Models\ProductServiceItem;

class ProformaInvoiceController extends Controller
{
    public function index(Request $request)
    {
        CompanyAccess::requirePermission('manage-sales-invoices');
        $request->validate(['search' => 'nullable|string|max:100', 'per_page' => 'sometimes|integer|min:1|max:100']);
        $invoices = ProformaInvoice::where('created_by', creatorId())->with('items')
            ->when($request->filled('search'), fn ($q) => $q->where('proforma_number', 'like', '%'.$request->input('search').'%'))
            ->latest()->paginate($request->integer('per_page', 15))->withQueryString();
        return $request->expectsJson() ? response()->json($invoices) : Inertia::render('Proformas/Index', [
            'invoices' => $invoices, 'filters' => $request->only('search'),
            'customers' => User::where('created_by', creatorId())->whereIn('type', ['client', 'customer'])->get(['id', 'name', 'email']),
            'products' => ProductServiceItem::where('created_by', creatorId())->where('is_active', true)->get(['id', 'name', 'sku', 'sale_price', 'unit']),
            'warehouses' => Warehouse::where('created_by', creatorId())->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        CompanyAccess::requirePermission('create-sales-invoices');
        $data = $request->validate([
            'customer_id' => ['required', Rule::exists('users', 'id')->where('created_by', creatorId())->whereIn('type', ['client', 'customer'])],
            'issue_date' => 'required|date', 'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'currency' => 'required|in:TZS', 'notes' => 'nullable|string|max:2000', 'payment_terms' => 'nullable|string|max:255',
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => ['required', Rule::exists('product_service_items', 'id')->where('created_by', creatorId())],
            'items.*.quantity' => 'required|numeric|min:0.0001|max:1000000',
            'items.*.unit_price' => 'required|numeric|min:0|max:1000000000',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100', 'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        $customer = User::findOrFail($data['customer_id']);
        $data['customer_name'] = $customer->name;
        $data['customer_email'] = $customer->email;
        $data['customer_phone'] = $customer->mobile_no;
        foreach ($data['items'] as &$item) {
            $product = ProductServiceItem::findOrFail($item['product_id']);
            $item['product_name'] = $product->name; $item['sku'] = $product->sku; $item['unit'] = $product->unit;
        }
        unset($item);
        $proforma = ProformaInvoiceService::create($data);
        return $request->expectsJson() ? response()->json($proforma, 201) : back()->with('success', __('Proforma created.'));
    }

    public function convert(Request $request, ProformaInvoice $proforma)
    {
        CompanyAccess::requirePermission('create-sales-invoices');
        CompanyAccess::check($proforma);
        $data = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('created_by', creatorId())->where('is_active', true)],
            'invoice_date' => 'required|date', 'due_date' => 'required|date|after_or_equal:invoice_date',
        ]);
        $invoice = ProformaInvoiceService::convert($proforma, $data);
        return $request->expectsJson() ? response()->json($invoice) : redirect()->route('sales-invoices.show', $invoice)->with('success', __('Proforma converted to a draft sales invoice.'));
    }

    public function cancel(Request $request, ProformaInvoice $proforma)
    {
        CompanyAccess::requirePermission('edit-sales-invoices');
        CompanyAccess::check($proforma);
        $proforma = ProformaInvoiceService::cancel($proforma);
        return $request->expectsJson() ? response()->json($proforma) : back()->with('success', __('Proforma cancelled.'));
    }

    public function print(ProformaInvoice $proforma)
    {
        CompanyAccess::requirePermission('print-sales-invoices');
        CompanyAccess::check($proforma);
        // Adapt the existing quotation print/PDF layout rather than duplicate it.
        return Inertia::render('SalesProposals/Print', [
            'documentTitle' => 'Proforma Invoice',
            'proposal' => array_merge($proforma->toArray(), [
                'proposal_number' => $proforma->proforma_number, 'proposal_date' => $proforma->issue_date,
                'due_date' => $proforma->expiry_date ?? $proforma->issue_date,
                'customer' => ['name' => $proforma->customer_name, 'email' => $proforma->customer_email],
                'items' => $proforma->items->map(fn ($item) => array_merge($item->toArray(), [
                    'product' => ['id' => $item->product_id, 'name' => $item->product_name, 'sku' => $item->sku],
                    'tax_percentage' => $item->tax_rate,
                    'discount_percentage' => (float) $item->quantity * (float) $item->unit_price > 0
                        ? round((float) $item->discount_amount / ((float) $item->quantity * (float) $item->unit_price) * 100, 2) : 0,
                ])),
            ]),
        ]);
    }
}
