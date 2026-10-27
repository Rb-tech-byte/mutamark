<?php

namespace App\Http\Controllers;

use App\Models\FinancialAuditLog;
use App\Services\FinancialControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FinancialAuditTrailController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-account')];
    }
    /**
     * Display the Financial Audit Trail & Compliance Ledger.
     */
    public function index(Request $request)
    {
        $query = FinancialAuditLog::where('created_by', creatorId());

        // Filters
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('audit_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('actor_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($eventType = $request->get('event_type')) {
            $query->where('event_type', $eventType);
        }

        if ($module = $request->get('module')) {
            $query->where('module', $module);
        }

        if ($severity = $request->get('severity')) {
            $query->where('severity', $severity);
        }

        $logs = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Compute KPIs
        $totalLogs = FinancialAuditLog::where('created_by',creatorId())->count();
        $immutabilityBlocks = FinancialAuditLog::where('created_by',creatorId())->where('event_type', 'IMMUTABILITY_BLOCKED')->count();
        $reversalsCount = FinancialAuditLog::where('created_by',creatorId())->where('event_type', 'JOURNAL_REVERSED')->count();
        $negativeStockBlocks = FinancialAuditLog::where('created_by',creatorId())->where('event_type', 'NEGATIVE_STOCK_BLOCKED')->count();
        $chainIntegrity = FinancialControlService::verifyAuditChainIntegrity();

        return Inertia::render('Accounting/AuditTrail/Index', [
            'logs' => $logs,
            'integrityExceptions' => \Illuminate\Support\Facades\DB::table('data_integrity_exceptions as e')->join('payment_transactions as p',function ($join) { $join->on('p.id','=','e.source_id')->where('e.source_table','=','payment_transactions'); })->where('p.creator_id',creatorId())->select('e.id','e.source_table','e.source_id','e.exception_type','e.resolution','e.recorded_at')->orderByDesc('e.id')->limit(100)->get(),
            'filters' => [
                'search' => $request->get('search', ''),
                'event_type' => $request->get('event_type', ''),
                'module' => $request->get('module', ''),
                'severity' => $request->get('severity', ''),
            ],
            'kpis' => [
                'total_logs' => $totalLogs,
                'immutability_blocks' => $immutabilityBlocks,
                'reversals_count' => $reversalsCount,
                'negative_stock_blocks' => $negativeStockBlocks,
                'chain_integrity' => $chainIntegrity,
            ],
        ]);
    }

    /**
     * Execute a Reversal Journal Voucher (RJV) from the UI.
     */
    public function reverseJournal(Request $request)
    {
        $request->validate([
            'journal_entry_id' => 'required|integer',
            'reason' => 'required|string|min:5|max:500',
        ]);

        try {
            $result = FinancialControlService::createReversalJournal(
                (int) $request->journal_entry_id,
                $request->reason
            );

            return back()->with('success', "Reversal Journal Voucher {$result['reversal_number']} executed successfully.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reconcileInvoice(Request $request, \App\Models\SalesInvoice $salesInvoice)
    {
        $data = $request->validate(['reason'=>'required|string|min:10|max:1000']);
        $invoice = FinancialControlService::reconcileInvoiceBalances($salesInvoice,$data['reason']);
        return $request->expectsJson() ? response()->json($invoice) : back()->with('success',__('Invoice balances reconciled to cleared allocations.'));
    }

    /**
     * Download CSV audit export for external regulatory / compliance audit.
     */
    public function exportCsv(Request $request)
    {
        $logs = FinancialAuditLog::where('created_by', creatorId())->orderBy('id', 'asc')->get();

        $filename = 'Mutamark-Financial-Audit-Ledger-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Audit Reference',
                'Timestamp (EAT)',
                'Event Type',
                'Module',
                'Document Reference',
                'Actor Name',
                'Actor Role',
                'IP Address',
                'Severity',
                'Action Summary',
                'Details',
                'Previous Hash',
                'Cryptographic Signature (SHA-256)',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->audit_number,
                    $log->created_at->toDateTimeString(),
                    $log->event_type,
                    $log->module,
                    $log->reference_number,
                    $log->actor_name,
                    $log->actor_role ?? 'System',
                    $log->ip_address ?? '127.0.0.1',
                    strtoupper($log->severity),
                    $log->action,
                    $log->details,
                    $log->previous_hash ?? 'GENESIS',
                    $log->current_hash,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API: Cryptographic Audit Chain Validation.
     */
    public function apiVerifyChain()
    {
        $integrity = FinancialControlService::verifyAuditChainIntegrity();
        return response()->json([
            'success' => true,
            'data' => $integrity,
        ]);
    }
}
