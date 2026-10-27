<?php

namespace Workdo\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Workdo\Account\Services\ReportService;

class ReportsController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        if(Auth::user()->can('manage-account-reports')){
            $currentYear = date('Y');
            $financialYear = [
                'year_start_date' => company_setting('financial_year_start') ?: "$currentYear-01-01",
                'year_end_date' => company_setting('financial_year_end') ?: "$currentYear-12-31",
            ];

            return Inertia::render('Account/Reports/Index', [
                'financialYear' => $financialYear,
                'accountingControls' => \App\Models\Setting::where('created_by',creatorId())->whereIn('key',['accounting_locked_until','inventory_adjustment_account_id','payroll_deductions_account_id','payroll_loans_receivable_account_id','withholding_payable_account_id'])->pluck('value','key'),
                'glAccounts' => \Workdo\Account\Models\ChartOfAccount::where('created_by',creatorId())->where('is_active',true)->orderBy('account_code')->get(['id','account_code','account_name']),
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function accountingControls(Request $request)
    {
        \App\Services\CompanyAccess::requirePermission('manage-account');
        $rules = ['financial_year_start'=>'required|date','financial_year_end'=>'required|date|after:financial_year_start','accounting_locked_until'=>'nullable|date|before_or_equal:today'];
        foreach (['inventory_adjustment_account_id','payroll_deductions_account_id','payroll_loans_receivable_account_id','withholding_payable_account_id'] as $key) $rules[$key] = ['nullable',\Illuminate\Validation\Rule::exists('chart_of_accounts','id')->where('created_by',creatorId())->where('is_active',true)];
        $data = $request->validate($rules);
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $before = \App\Models\Setting::where('created_by',creatorId())->whereIn('key',array_keys($data))->pluck('value','key')->all();
            foreach ($data as $key=>$value) \App\Models\Setting::updateOrCreate(['created_by'=>creatorId(),'key'=>$key],['value'=>$value ?? '','is_public'=>false]);
            \App\Models\FinancialAuditLog::record(['event_type'=>'ACCOUNTING_CONFIG_CHANGED','module'=>'Accounting','reference_number'=>'ACCOUNTING-CONTROLS','action'=>'Updated accounting controls','old_values'=>$before,'new_values'=>$data]);
        });
        \Illuminate\Support\Facades\Cache::forget('company_settings_'.creatorId());
        return $request->expectsJson() ? response()->json($data) : back()->with('success',__('Accounting controls updated.'));
    }

    public function invoiceAging(Request $request)
    {
        $filters = [
            'as_of_date' => $request->as_of_date ?: date('Y-m-d'),
        ];

        $data = $this->reportService->getInvoiceAging($filters);
        return response()->json($data);
    }

    public function billAging(Request $request)
    {
        $filters = [
            'as_of_date' => $request->as_of_date ?: date('Y-m-d'),
        ];

        $data = $this->reportService->getBillAging($filters);
        return response()->json($data);
    }

    public function taxSummary(Request $request)
    {
        \App\Services\CompanyAccess::requirePermission('view-tax-summary');
        $currentYear = date('Y');
        $filters = [
            'from_date' => $request->from_date ?: "$currentYear-01-01",
            'to_date' => $request->to_date ?: "$currentYear-12-31",
        ];

        $data = $this->reportService->getTaxSummary($filters);
        $data['withholding_taxes'] = \Illuminate\Support\Facades\DB::table('withholding_taxes')->where('created_by',creatorId())->whereBetween('payment_date',[$filters['from_date'],$filters['to_date']])->orderByDesc('id')->limit(200)->get();
        $data['remittance_banks'] = \Workdo\Account\Models\BankAccount::where('created_by',creatorId())->where('is_active',true)->whereNotNull('gl_account_id')->get(['id','account_name']);
        return response()->json($data);
    }

    public function remitWithholding(Request $request, int $id)
    {
        \App\Services\CompanyAccess::requirePermission('manage-account');
        $data=$request->validate(['bank_account_id'=>['required',\Illuminate\Validation\Rule::exists('bank_accounts','id')->where('created_by',creatorId())->where('is_active',true)],'remittance_date'=>'required|date|before_or_equal:today']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($id,$data) {
            $tax=\Illuminate\Support\Facades\DB::table('withholding_taxes')->where('created_by',creatorId())->lockForUpdate()->find($id); abort_unless($tax,404);
            if ($tax->status==='remitted') return;
            $bank=\Workdo\Account\Models\BankAccount::where('created_by',creatorId())->lockForUpdate()->findOrFail($data['bank_account_id']);
            abort_unless($bank->gl_account_id && $tax->gl_account_id,422,'Map the bank and withholding liability GL accounts.');
            app(\Workdo\Account\Services\JournalService::class)->postControlledJournal('withholding_remittance',$id,$data['remittance_date'],[['account_id'=>$tax->gl_account_id,'debit_amount'=>$tax->wht_amount,'credit_amount'=>0],['account_id'=>$bank->gl_account_id,'debit_amount'=>0,'credit_amount'=>$tax->wht_amount]]);
            app(\Workdo\Account\Services\BankTransactionsService::class)->createTaxRemittance($bank,(float)$tax->wht_amount,$tax->wht_number,$data['remittance_date']);
            \Illuminate\Support\Facades\DB::table('withholding_taxes')->where('id',$id)->update(['status'=>'remitted','remittance_date'=>$data['remittance_date'],'updated_at'=>now()]);
        },3);
        return response()->json(['success'=>true]);
    }

    public function customerBalance(Request $request)
    {
        $filters = [
            'as_of_date' => $request->as_of_date ?: date('Y-m-d'),
            'show_zero_balances' => $request->show_zero_balances === 'true',
        ];

        $data = $this->reportService->getCustomerBalanceSummary($filters);
        return response()->json($data);
    }

    public function vendorBalance(Request $request)
    {
        $filters = [
            'as_of_date' => $request->as_of_date ?: date('Y-m-d'),
            'show_zero_balances' => $request->show_zero_balances === 'true',
        ];

        $data = $this->reportService->getVendorBalanceSummary($filters);
        return response()->json($data);
    }

    public function printInvoiceAging(Request $request)
    {
        if(Auth::user()->can('print-invoice-aging')){
            $filters = ['as_of_date' => $request->as_of_date ?: date('Y-m-d')];
            $data = $this->reportService->getInvoiceAging($filters);
            return Inertia::render('Account/Reports/Print/InvoiceAging', ['data' => $data, 'filters' => $filters]);
        }
        else
        {
             return back()->with('error', __('Permission denied'));
        }
    }

    public function printBillAging(Request $request)
    {
        if(Auth::user()->can('print-bill-aging')){
            $filters = ['as_of_date' => $request->as_of_date ?: date('Y-m-d')];
            $data = $this->reportService->getBillAging($filters);
            return Inertia::render('Account/Reports/Print/BillAging', ['data' => $data, 'filters' => $filters]);
        }
        else
        {
             return back()->with('error', __('Permission denied'));
        }
    }

    public function printTaxSummary(Request $request)
    {
        if(Auth::user()->can('print-tax-summary')){
             $currentYear = date('Y');
            $filters = [
                'from_date' => $request->from_date ?: "$currentYear-01-01",
                'to_date' => $request->to_date ?: "$currentYear-12-31",
            ];
            $data = $this->reportService->getTaxSummary($filters);
            return Inertia::render('Account/Reports/Print/TaxSummary', ['data' => $data, 'filters' => $filters]);
        }
        else
        {
                return back()->with('error', __('Permission denied'));
        }
    }

    public function printCustomerBalance(Request $request)
    {
        if(Auth::user()->can('print-customer-balance')){
            $filters = [
                'as_of_date' => $request->as_of_date ?: date('Y-m-d'),
                'show_zero_balances' => $request->show_zero_balances === 'true',
                ];
            $data = $this->reportService->getCustomerBalanceSummary($filters);
            return Inertia::render('Account/Reports/Print/CustomerBalance', ['data' => $data, 'filters' => $filters]);
        }
        else{
                return back()->with('error', __('Permission denied'));
        }
    }

    public function printVendorBalance(Request $request)
    {
        if(Auth::user()->can('print-vendor-balance')){
            $filters = [
                'as_of_date' => $request->as_of_date ?: date('Y-m-d'),
                'show_zero_balances' => $request->show_zero_balances === 'true',
            ];
            $data = $this->reportService->getVendorBalanceSummary($filters);
            return Inertia::render('Account/Reports/Print/VendorBalance', ['data' => $data, 'filters' => $filters]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function customerDetail($customerId, Request $request)
    {
        if(Auth::user()->can('view-customer-detail-report')){
            $filters = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];

            $data = $this->reportService->getCustomerDetail($customerId, $filters);

            if (!$data) {
                return back()->with('error', __('Customer not found'));
            }

            return Inertia::render('Account/Reports/CustomerDetail', [
                'customerData' => $data,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function vendorDetail($vendorId, Request $request)
    {
        if(Auth::user()->can('view-vendor-detail-report')){
            $filters = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];

            $data = $this->reportService->getVendorDetail($vendorId, $filters);

            if (!$data) {
                return back()->with('error', __('Vendor not found'));
            }

            return Inertia::render('Account/Reports/VendorDetail', [
                'vendorData' => $data,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function printCustomerDetail($customerId, Request $request)
    {
        if(Auth::user()->can('print-customer-detail-report')){
            $filters = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];

            $data = $this->reportService->getCustomerDetail($customerId, $filters);

            if (!$data) {
                return back()->with('error', __('Customer not found'));
            }

            return Inertia::render('Account/Reports/Print/CustomerDetail', [
                'data' => $data,
                'filters' => $filters,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function printVendorDetail($vendorId, Request $request)
    {
        if(Auth::user()->can('print-vendor-detail-report')){
            $filters = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];

            $data = $this->reportService->getVendorDetail($vendorId, $filters);

            if (!$data) {
                return back()->with('error', __('Vendor not found'));
            }

            return Inertia::render('Account/Reports/Print/VendorDetail', [
                'data' => $data,
                'filters' => $filters,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }
}
