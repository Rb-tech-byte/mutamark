<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'document_type',
        'document_id',
        'document_number',
        'title',
        'amount',
        'currency',
        'current_tier',
        'max_tier',
        'status',
        'requester_id',
        'current_approver_id',
        'current_approver_role',
        'verification_code',
        'signed_at',
        'signed_by',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'current_tier' => 'integer',
        'max_tier' => 'integer',
        'signed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = \App\Services\DocumentNumberService::next('APR');
            }
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function signedByUser()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class, 'approval_request_id')->orderBy('id', 'asc');
    }

    /**
     * Retrieve the underlying Eloquent model for the target document
     */
    public function getDocumentInstance()
    {
        switch ($this->document_type) {
            case 'purchase_invoice':
                return PurchaseInvoice::find($this->document_id);
            case 'expense':
                return class_exists(\Workdo\Account\Models\Expense::class) 
                    ? \Workdo\Account\Models\Expense::find($this->document_id) 
                    : null;
            case 'sales_proposal':
                return SalesProposal::find($this->document_id);
            case 'asset_disposal':
                return AssetDisposal::find($this->document_id);
            default:
                return null;
        }
    }

    /**
     * Generate Cryptographic SHA-256 Signature for Approved Documents
     */
    public function generateSignature(int $userId): string
    {
        $appKey = config('app.key', 'MutamarkSecretSignKey2026');
        $raw = "{$this->request_number}|{$this->document_type}|{$this->document_number}|{$this->amount}|{$this->currency}|{$userId}|" . Carbon::now()->toIso8601String();
        $hash = hash_hmac('sha256', $raw, $appKey);

        $this->verification_code = $hash;
        $this->signed_at = Carbon::now();
        $this->signed_by = $userId;
        $this->save();

        // Register in Public DocumentVerification table for QR scanner verification (/verify/document/{hash})
        if (class_exists(DocumentVerification::class)) {
            $signer = User::find($userId);
            $verification = DocumentVerification::updateOrCreate([
                'verification_hash' => $hash,
            ], [
                'document_type' => 'approval_' . $this->document_type,
                'document_id' => $this->id,
                'document_number' => $this->request_number . ' / ' . $this->document_number,
                'issuer_name' => 'Mutamark Enterprises Ltd',
                'party_name' => $this->title,
                'total_amount' => $this->amount,
                'tax_amount' => 0.00,
                'currency' => $this->currency,
                'issue_date' => Carbon::now()->toDateString(),
                'status' => 'verified',
                'signatory_name' => $signer ? $signer->name : 'Executive Signatory',
                'signatory_title' => 'Managing Director / Authorized Signatory',
                'qr_payload' => url("/verify/document/{$hash}"),
                'verified_at' => Carbon::now(),
                'created_by' => $userId,
            ]);
        }

        if (isset($verification) && ($source = $this->getDocumentInstance())) {
            $verification = \App\Services\DocumentVerificationService::bindSignature($verification, $source, $userId);
            $hash = $verification->verification_hash;
            $this->update(['verification_code' => $hash]);
        }
        return $hash;
    }
}
