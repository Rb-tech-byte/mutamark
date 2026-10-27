<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\PurchaseInvoice;
use App\Models\ThreeWayMatchAuthorization;
use App\Models\ThreeWayMatchConfig;
use App\Services\ThreeWayMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ThreeWayMatchController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-account')];
    }
    /**
     * Get authenticated creator/company ID safely.
     */
    protected function getCompanyId(): int
    {
        if (function_exists('creatorId')) {
            $cid = creatorId();
            if ($cid) {
                return (int) $cid;
            }
        }
        return Auth::check() ? Auth::id() : 1;
    }

    /**
     * Display the 3-Way Matching Audit & AP Reconciliation Console.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $config = ThreeWayMatchConfig::getForCompany($companyId);

        $grns = GoodsReceivedNote::with([
            'items.product',
            'purchaseInvoice.items',
            'vendor',
            'warehouse',
            'receiver',
        ])
        ->where('created_by', $companyId)
        ->orderBy('id', 'desc')
        ->get();

        $audits = [];
        $totalAudited = count($grns);
        $clearedCount = 0;
        $varianceHoldCount = 0;
        $pendingInvoiceCount = 0;
        $totalClearedDisbursementTzs = 0;

        foreach ($grns as $grn) {
            $audit = ThreeWayMatchService::auditGrnMatch($grn, $config);

            // Compute authorizations
            $auth = ThreeWayMatchAuthorization::with(['clearedByUser'])->where('grn_id', $grn->id)->first();
            $audit['authorization'] = $auth;

            if ($audit['authorization'] && $audit['authorization']->disbursement_cleared) {
                $clearedCount++;
                $totalClearedDisbursementTzs += (float) $grn->total_amount;
            } elseif ($audit['match_status'] === 'variance_hold') {
                $varianceHoldCount++;
            } elseif ($audit['match_status'] === 'pending_invoice') {
                $pendingInvoiceCount++;
            }

            $audits[] = $audit;
        }

        // Available Invoices for manual linking
        $availableInvoices = PurchaseInvoice::where('created_by', $companyId)
            ->whereNull('debit_note_applied')
            ->orderBy('id', 'desc')
            ->get(['id', 'invoice_number', 'total_amount', 'status', 'invoice_date']);

        return Inertia::render('Procurement/ThreeWayMatch/Index', [
            'audits' => $audits,
            'config' => $config,
            'kpi' => [
                'total_audited_count' => $totalAudited,
                'cleared_count' => $clearedCount,
                'variance_hold_count' => $varianceHoldCount,
                'pending_invoice_count' => $pendingInvoiceCount,
                'total_cleared_disbursement_tzs' => $totalClearedDisbursementTzs,
            ],
            'available_invoices' => $availableInvoices,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Update 3-Way Matching Tolerance Rules & Thresholds.
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'qty_tolerance_percent' => 'required|numeric|min:0|max:100',
            'price_tolerance_percent' => 'required|numeric|min:0|max:100',
            'max_variance_amount_tzs' => 'required|numeric|min:0',
            'require_qc_passed' => 'required|boolean',
            'auto_hold_on_variance' => 'required|boolean',
        ]);

        $companyId = $this->getCompanyId();
        $config = ThreeWayMatchConfig::getForCompany($companyId);
        $config->update($validated);

        return redirect()->back()->with('success', 'Three-Way Matching tolerance rules and variance thresholds updated.');
    }

    /**
     * Authorize Accounts Payable disbursement release.
     */
    public function authorizeDisbursement(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id() ?: 1;
        $notes = $validated['notes'] ?? 'Authorized by finance audit officer.';

        $auth = ThreeWayMatchService::authorizeDisbursement($id, $userId, $notes);

        return redirect()->back()->with('success', "Disbursement release cleared for GRN {$auth->grn->grn_number}. Payment authorized.");
    }

    /**
     * Flag match as disputed and block disbursement.
     */
    public function flagDispute(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $auth = ThreeWayMatchService::flagDiscrepancy($id, $validated['notes']);

        return redirect()->back()->with('success', "Dispute recorded on GRN {$auth->grn->grn_number}. AP payment hold placed.");
    }

    /**
     * Link an existing Supplier Bill / Invoice to a GRN.
     */
    public function linkInvoice(Request $request)
    {
        $validated = $request->validate([
            'grn_id' => 'required|integer|exists:goods_received_notes,id',
            'invoice_id' => 'required|integer|exists:purchase_invoices,id',
        ]);

        $companyId = $this->getCompanyId();
        ThreeWayMatchService::linkInvoiceToGrn($validated['grn_id'], $validated['invoice_id'], $companyId);

        return redirect()->back()->with('success', 'Supplier invoice linked to GRN. 3-Way match re-evaluated.');
    }
}
