<?php

namespace App\Http\Controllers;

use App\Models\ApprovalAction;
use App\Models\ApprovalPolicy;
use App\Models\ApprovalRequest;
use App\Models\AssetDisposal;
use Workdo\Account\Models\Expense;
use App\Models\PurchaseInvoice;
use App\Models\SalesProposal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ApprovalWorkflowController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-account', only: ['index', 'storePolicy', 'togglePolicy'])];
    }
    /**
     * Safely obtain creator/company ID
     */
    protected function getCreatorId()
    {
        if (function_exists('creatorId')) {
            $cid = creatorId();
            if ($cid) {
                return $cid;
            }
        }
        return Auth::check() ? Auth::id() : 1;
    }

    /**
     * Display Multi-Tier Approval Workflow Dashboard and Management View.
     */
    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $user = Auth::user();
        $userRole = $user ? ($user->type ?? 'company') : 'company';

        // 1. Pending My Approval: Requests requiring attention from this user/role
        $myPendingQuery = ApprovalRequest::with(['requester', 'currentApprover', 'actions.actor'])
            ->where('creator_id', creatorId())
            ->whereIn('status', ['submitted', 'under_review']);

        // If not super-company/admin, filter by approver ID or role
        if ($userRole !== 'company' && $userRole !== 'super admin') {
            $myPendingQuery->where(function ($q) use ($userId, $userRole) {
                $q->where('current_approver_id', $userId)
                  ->orWhere('current_approver_role', $userRole)
                  ->orWhereNull('current_approver_role');
            });
        }
        $myPending = $myPendingQuery->orderBy('id', 'desc')->get();

        // 2. All Approval Requests
        $allRequests = ApprovalRequest::with(['requester', 'currentApprover', 'signedByUser', 'actions.actor'])
            ->where('creator_id', creatorId())
            ->orderBy('id', 'desc')
            ->get();

        // 3. Policies & Escalation Thresholds
        $policies = ApprovalPolicy::with('approver')
            ->where('creator_id', creatorId())
            ->orderBy('document_type')
            ->orderBy('tier_level')
            ->get();

        // 4. Recent Audit Actions
        $recentActions = ApprovalAction::with(['actor', 'approvalRequest'])
            ->whereHas('approvalRequest', fn ($q) => $q->where('creator_id', creatorId()))
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        // 5. KPIs
        $totalRequests = ApprovalRequest::where('creator_id', creatorId())->count();
        $approvedCount = ApprovalRequest::where('creator_id', creatorId())->whereIn('status', ['approved', 'signed', 'issued', 'closed'])->count();
        $totalApprovedAmount = ApprovalRequest::where('creator_id', creatorId())->whereIn('status', ['approved', 'signed', 'issued', 'closed'])->sum('amount');
        $pendingCount = ApprovalRequest::where('creator_id', creatorId())->whereIn('status', ['submitted', 'under_review'])->count();

        // Users for assignment
        $users = User::where(fn ($q) => $q->where('created_by', creatorId())->orWhere('id', creatorId()))->select('id', 'name', 'email', 'type')->orderBy('name')->get();

        return Inertia::render('Approvals/Index', [
            'metrics' => [
                'pending_my_approval' => $myPending->count(),
                'total_pending' => $pendingCount,
                'total_requests' => $totalRequests,
                'approved_count' => $approvedCount,
                'total_approved_amount' => (float) $totalApprovedAmount,
            ],
            'my_pending' => $myPending,
            'all_requests' => $allRequests,
            'policies' => $policies,
            'recent_actions' => $recentActions,
            'users' => $users,
            'delegations' => \App\Models\ApprovalDelegation::where('created_by', creatorId())->where('delegator_id', auth()->id())->with('delegate:id,name')->latest()->get(),
        ]);
    }

    /**
     * Store or update an Approval Policy rule.
     */
    public function storePolicy(Request $request)
    {
        \App\Services\CompanyAccess::requirePermission('manage-roles');
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'document_type' => 'required|string|in:purchase_invoice,expense,sales_proposal,asset_disposal',
            'tier_level' => 'required|integer|min:1|max:5',
            'tier_name' => 'required|string|max:100',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'required_role' => 'nullable|string|max:50',
            'approver_id' => 'nullable|exists:users,id',
            'requires_digital_signature' => 'boolean',
            'enforce_segregation_of_duties' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $companyId = $this->getCreatorId();
        if (!empty($validated['id'])) {
            ApprovalPolicy::where('creator_id', $companyId)->findOrFail($validated['id']);
        }
        if (!empty($validated['approver_id'])) {
            User::where(fn ($q) => $q->where('created_by', $companyId)->orWhere('id', $companyId))->findOrFail($validated['approver_id']);
        }

        ApprovalPolicy::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            array_merge($validated, [
                'creator_id' => $companyId,
                'created_by' => Auth::id() ?? 1,
            ])
        );

        return redirect()->back()->with('success', 'Approval Policy saved successfully.');
    }

    /**
     * Toggle policy activation status.
     */
    public function togglePolicy($id)
    {
        \App\Services\CompanyAccess::requirePermission('manage-roles');
        $policy = ApprovalPolicy::where('creator_id', creatorId())->findOrFail($id);
        $policy->is_active = !$policy->is_active;
        $policy->save();

        return redirect()->back()->with('success', 'Policy status updated.');
    }

    /**
     * Execute an action on an approval request.
     */
    public function processAction(Request $request, $id)
    {
        $data = $request->validate(['action' => 'required|in:review,return,approve,reject,sign,issue,close', 'comments' => 'nullable|string|max:1000']);
        $approval = \App\Services\ApprovalWorkflowService::act((int) $id, $data, fn ($record, $state) => $this->syncDocumentStatus($record, $state));
        return $request->expectsJson() ? response()->json($approval) : back()->with('success', __('Approval request updated.'));
    }

    /**
     * Submit an existing business document into the Multi-Tier Approval workflow
     */
    public function submitDocument(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:purchase_invoice,expense,sales_proposal,asset_disposal',
            'document_id' => 'required|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        $tables = ['purchase_invoice' => 'purchase_invoices', 'expense' => 'expenses', 'sales_proposal' => 'sales_proposals', 'asset_disposal' => 'asset_disposals'];
        \App\Services\TransactionSafety::requireTransactionalTables(['approval_requests', 'approval_actions', 'document_sequences', $tables[$validated['document_type']]]);
        return DB::transaction(function () use ($validated, $request) {
        $companyId = $this->getCreatorId();
        $userId = Auth::id() ?? 1;

        // Fetch document details
        $doc = null;
        $docNumber = '';
        $docTitle = '';
        $amount = 0.0;

        switch ($validated['document_type']) {
            case 'purchase_invoice':
                $doc = PurchaseInvoice::lockForUpdate()->find($validated['document_id']);
                if ($doc) {
                    $docNumber = $doc->invoice_number;
                    $docTitle = "Supplier Invoice: " . ($doc->vendor->company_name ?? 'Vendor');
                    $amount = (float) $doc->total_amount;
                }
                break;
            case 'expense':
                $doc = Expense::lockForUpdate()->find($validated['document_id']);
                if ($doc) {
                    $docNumber = $doc->expense_number;
                    $docTitle = "Operational Expense: " . ($doc->description ?? 'Expense');
                    $amount = (float) $doc->amount;
                }
                break;
            case 'sales_proposal':
                $doc = SalesProposal::lockForUpdate()->find($validated['document_id']);
                if ($doc) {
                    $docNumber = $doc->proposal_number;
                    $docTitle = "Sales Proposal Quotation: " . ($doc->customer->name ?? 'Customer');
                    $amount = (float) $doc->total_amount;
                }
                break;
            case 'asset_disposal':
                $doc = AssetDisposal::lockForUpdate()->find($validated['document_id']);
                if ($doc) {
                    $docNumber = $doc->disposal_number;
                    $docTitle = "Asset Disposal Write-off: " . ($doc->asset->name ?? 'Fixed Asset');
                    $amount = (float) $doc->book_value_at_disposal;
                }
                break;
        }

        if (!$doc) {
            return redirect()->back()->with('error', 'Document not found.');
        }
        \App\Services\CompanyAccess::check($doc);
        $permissions = ['purchase_invoice' => 'create-purchase-invoices', 'expense' => 'create-expenses', 'sales_proposal' => 'create-sales-proposals', 'asset_disposal' => 'manage-account'];
        \App\Services\CompanyAccess::requirePermission($permissions[$validated['document_type']]);
        if (in_array($validated['document_type'], ['purchase_invoice', 'expense']) && $doc->status !== 'draft') {
            abort(409, 'Only draft documents can enter the approval workflow.');
        }

        // Check if approval request already exists for this document
        $existing = ApprovalRequest::where('document_type', $validated['document_type'])
            ->where('creator_id', $companyId)
            ->where('document_id', $validated['document_id'])
            ->whereNotIn('status', ['rejected', 'closed'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', "Approval request {$existing->request_number} already active for this document.");
        }

        // Determine maximum required tier based on active policies
        $policies = ApprovalPolicy::active()
            ->where('creator_id', $companyId)
            ->where('document_type', $validated['document_type'])
            ->where('min_amount', '<=', $amount)
            ->where(fn ($q) => $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount))
            ->orderBy('tier_level', 'asc')
            ->get();

        $maxTier = $policies->max('tier_level') ?? 1;
        $firstPolicy = $policies->first();
        abort_unless($firstPolicy, 422, 'Configure an eligible approval policy first.');

        $approvalReq = ApprovalRequest::create([
            'document_type' => $validated['document_type'],
            'document_id' => $validated['document_id'],
            'document_number' => $docNumber ?: "DOC-{$validated['document_id']}",
            'title' => $docTitle ?: "Document Approval Request",
            'amount' => $amount,
            'currency' => 'TZS',
            'current_tier' => $firstPolicy->tier_level,
            'max_tier' => max(1, $maxTier),
            'status' => 'submitted',
            'requester_id' => $userId,
            'current_approver_role' => $firstPolicy ? $firstPolicy->required_role : 'officer',
            'current_approver_id' => $firstPolicy ? $firstPolicy->approver_id : null,
            'notes' => $validated['notes'] ?? null,
            'creator_id' => $companyId,
            'created_by' => $userId,
        ]);

        // Log submission action
        ApprovalAction::create([
            'approval_request_id' => $approvalReq->id,
            'tier_level' => $firstPolicy->tier_level,
            'actor_id' => $userId,
            'action' => 'submit',
            'previous_status' => 'draft',
            'new_status' => 'submitted',
            'comments' => $validated['notes'] ?? 'Submitted into Multi-Tier Approval workflow',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Approval Request {$approvalReq->request_number} created successfully.");
        }, 3);
    }

    /**
     * Synchronize the underlying document status with approval state
     */
    protected function syncDocumentStatus(ApprovalRequest $approvalReq, string $approvalState)
    {
        $doc = $approvalReq->getDocumentInstance();
        abort_unless($doc, 404, 'The approval document no longer exists.');
        \App\Services\CompanyAccess::check($doc);

        switch ($approvalReq->document_type) {
            case 'purchase_invoice':
                if ($approvalState === 'issued' && $doc->status === 'draft') {
                    \App\Services\FinancialControlService::postInvoice($doc);
                }
                break;
            case 'expense':
                if ($doc->status === 'draft' && in_array($approvalState, ['approved', 'signed', 'issued'])) {
                    $doc->status = 'approved';
                    $doc->save();
                }
                break;
            case 'sales_proposal':
                if ($approvalState === 'issued' && $doc->status === 'draft') {
                    $doc->status = 'sent';
                    $doc->save();
                }
                break;
            case 'asset_disposal':
                if (in_array($approvalState, ['approved', 'signed', 'issued'])) {
                    $doc->approved_by = Auth::id() ?? 1;
                    $doc->save();
                }
                break;
        }
    }
}
