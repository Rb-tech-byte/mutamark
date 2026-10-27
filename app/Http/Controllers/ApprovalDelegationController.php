<?php

namespace App\Http\Controllers;

use App\Models\ApprovalDelegation;
use App\Services\CompanyAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApprovalDelegationController extends Controller
{
    public function store(Request $request)
    {
        CompanyAccess::requirePermission('manage-account');
        $data = $request->validate([
            'delegate_id' => ['required', 'different:delegator_id', Rule::exists('users', 'id')->where(fn ($q) => $q->where('created_by', creatorId())->orWhere('id', creatorId()))],
            'start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255', 'document_type' => 'nullable|in:purchase_invoice,expense,sales_proposal,asset_disposal',
        ]);
        abort_if((int) $data['delegate_id'] === auth()->id(), 422, 'You cannot delegate to yourself.');
        $delegation = ApprovalDelegation::create([
            'delegator_id' => auth()->id(), 'delegate_id' => $data['delegate_id'],
            'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'reason' => $data['reason'],
            'document_types' => $data['document_type'] ?? null, 'is_active' => true, 'created_by' => creatorId(),
        ]);
        return $request->expectsJson() ? response()->json($delegation, 201) : back()->with('success', __('Approval delegation saved.'));
    }

    public function revoke(Request $request, ApprovalDelegation $delegation)
    {
        CompanyAccess::check($delegation);
        abort_unless((int) $delegation->delegator_id === auth()->id(), 403);
        $delegation->update(['is_active' => false]);
        return $request->expectsJson() ? response()->json($delegation) : back()->with('success', __('Delegation revoked.'));
    }
}
