<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\TraFiscalReceipt;
use App\Models\TraZReport;
use App\Services\TraFiscalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TraFiscalController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-sales-invoices') && !Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $creatorId = creatorId();
        $activeZReport = TraFiscalService::getOrCreateActiveZReport($creatorId);

        // Fiscal Receipts Query
        $receipts = TraFiscalReceipt::query()
            ->where('created_by', $creatorId)
            ->when($request->search, function ($q, $search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->when($request->status, fn($q, $st) => $q->where('status', $st))
            ->latest('fiscalized_at')
            ->paginate($request->per_page ?: 10, ['*'], 'receipts_page')
            ->withQueryString();

        // Historical Z-Reports Query
        $zReports = TraZReport::query()
            ->where('created_by', $creatorId)
            ->with('closedByUser:id,name')
            ->latest('report_date')
            ->paginate(10, ['*'], 'z_reports_page')
            ->withQueryString();

        // Current Fiscal Day Statistics
        $statistics = [
            'vfd_status' => 'ONLINE',
            'vfd_device_serial' => TraFiscalService::VFD_SERIAL,
            'company_tin' => TraFiscalService::COMPANY_TIN,
            'company_vrn' => TraFiscalService::COMPANY_VRN,
            'active_z_number' => $activeZReport->z_report_number,
            'active_z_status' => $activeZReport->status,
            'today_receipts_count' => $activeZReport->total_receipts_count,
            'today_gross_turnover' => (float)$activeZReport->gross_turnover,
            'today_net_turnover' => (float)$activeZReport->net_taxable_turnover,
            'today_vat_18' => (float)$activeZReport->total_vat_18,
            'cumulative_turnover' => (float)$activeZReport->cumulative_turnover_to_date,
        ];

        // Fetch un-fiscalized sales invoices available for 1-click fiscalization
        $unfiscalizedInvoices = SalesInvoice::where('created_by', $creatorId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereDoesntHave('items', fn($q) => $q) // or where doesn't have receipt
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('tra_fiscal_receipts')
                    ->whereColumn('tra_fiscal_receipts.source_id', 'sales_invoices.id')
                    ->where('tra_fiscal_receipts.source_type', 'sales_invoice');
            })
            ->latest()
            ->take(5)
            ->get(['id', 'invoice_number', 'total_amount', 'invoice_date', 'status']);

        return Inertia::render('TraFiscal/Index', [
            'receipts' => $receipts,
            'zReports' => $zReports,
            'activeZReport' => $activeZReport,
            'statistics' => $statistics,
            'unfiscalizedInvoices' => $unfiscalizedInvoices,
        ]);
    }

    /**
     * Fiscalize an invoice
     */
    public function fiscalizeInvoice(SalesInvoice $salesInvoice)
    {
        if (!Auth::user()->can('manage-sales-invoices') && !Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $receipt = TraFiscalService::fiscalizeSalesInvoice($salesInvoice);

        return back()->with('success', __("Official TRA Fiscal Receipt :receipt issued successfully with QR verification code.", [
            'receipt' => $receipt->receipt_number,
        ]));
    }

    /**
     * Show official printable TRA Fiscal Slip
     */
    public function showReceipt(TraFiscalReceipt $traFiscalReceipt)
    {
        $traFiscalReceipt->load(['invoice.items.product', 'invoice.customer', 'verification']);

        return Inertia::render('TraFiscal/Receipt', [
            'receipt' => $traFiscalReceipt,
        ]);
    }

    /**
     * Close and transmit daily Z-Report
     */
    public function closeZReport(Request $request)
    {
        if (!Auth::user()->can('manage-sales-invoices') && !Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $closedReport = TraFiscalService::closeDailyZReport(Auth::id(), creatorId());

        return back()->with('success', __("Daily Z-Report :z closed and transmitted to TRA Gateway. Acknowledgment Token: :token", [
            'z' => $closedReport->z_report_number,
            'token' => $closedReport->tra_ack_token,
        ]));
    }

    /**
     * Show official statutory Z-Report summary sheet
     */
    public function showZReport(TraZReport $traZReport)
    {
        $traZReport->load(['receipts', 'closedByUser']);

        return Inertia::render('TraFiscal/ZReport', [
            'zReport' => $traZReport,
        ]);
    }
}
