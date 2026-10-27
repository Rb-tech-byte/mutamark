<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalPolicy;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public static function act(int $id, array $data, callable $sync): ApprovalRequest
    {
        TransactionSafety::requireTransactionalTables(['approval_requests', 'approval_actions', 'approval_delegations', 'document_verifications']);
        return DB::transaction(function () use ($id, $data, $sync) {
            $approval = ApprovalRequest::where('creator_id', creatorId())->lockForUpdate()->findOrFail($id);
            $actor = auth()->user(); $action = $data['action'];
            $assigned = $approval->current_approver_id
                ? (int) $approval->current_approver_id === (int) $actor->id
                : ($approval->current_approver_role && ($actor->type === $approval->current_approver_role || $actor->hasRole($approval->current_approver_role)));
            $delegated = $approval->current_approver_id && ApprovalDelegation::where('created_by', creatorId())
                ->where('delegator_id', $approval->current_approver_id)->where('delegate_id', $actor->id)->where('is_active', true)
                ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->get()
                ->contains(fn ($delegation) => !$delegation->document_types || in_array($approval->document_type, explode(',', $delegation->document_types), true));
            abort_unless($assigned || $delegated, 403, 'You are not the assigned approver or an active delegate.');
            $policy = ApprovalPolicy::where('creator_id', creatorId())->active()->where('document_type', $approval->document_type)
                ->where('tier_level', $approval->current_tier)->first();
            if (in_array($action, ['approve', 'sign', 'issue']) && (int) $approval->requester_id === (int) $actor->id && ($policy?->enforce_segregation_of_duties ?? true)) {
                abort(403, 'You cannot approve, sign or issue your own request.');
            }
            $allowed = [
                'review' => ['submitted'], 'return' => ['submitted', 'under_review'],
                'reject' => ['submitted', 'under_review'], 'approve' => ['submitted', 'under_review'],
                'sign' => ['approved'], 'issue' => ['signed'], 'close' => ['issued', 'rejected'],
            ];
            if ($action === 'issue' && $policy && !$policy->requires_digital_signature) $allowed['issue'][] = 'approved';
            if (!isset($allowed[$action]) || !in_array($approval->status, $allowed[$action], true)) {
                throw ValidationException::withMessages(['action' => __('This action is not allowed in the current approval state.')]);
            }
            $previous = $approval->status; $tier = $approval->current_tier;
            $states = ['review' => 'under_review', 'return' => 'returned', 'reject' => 'rejected', 'approve' => 'approved', 'sign' => 'signed', 'issue' => 'issued', 'close' => 'closed'];
            $approval->status = $states[$action];
            if ($action === 'approve' && $tier < $approval->max_tier) {
                $next = ApprovalPolicy::where('creator_id', creatorId())->active()->where('document_type', $approval->document_type)
                    ->where('tier_level', '>', $tier)->where('min_amount', '<=', $approval->amount)
                    ->where(fn ($q) => $q->whereNull('max_amount')->orWhere('max_amount', '>=', $approval->amount))->orderBy('tier_level')->first();
                if (!$next) throw ValidationException::withMessages(['policy' => __('No eligible next-tier policy is configured.')]);
                $approval->current_tier = $next->tier_level;
                $approval->current_approver_id = $next->approver_id;
                $approval->current_approver_role = $next->required_role;
                $approval->status = 'under_review';
            }
            $approval->save();
            if ($action === 'sign') $approval->generateSignature($actor->id);
            $sync($approval, $approval->status);
            ApprovalAction::create([
                'approval_request_id' => $approval->id, 'tier_level' => $tier, 'actor_id' => $actor->id,
                'action' => $action, 'previous_status' => $previous, 'new_status' => $approval->status,
                'comments' => $data['comments'] ?? null, 'ip_address' => request()->ip(),
                'digital_signature' => $approval->verification_code,
            ]);
            return $approval->fresh();
        }, 3);
    }
}
