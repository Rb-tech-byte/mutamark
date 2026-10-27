<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVerification extends Model
{
    protected $hidden = ['signed_payload', 'source_model'];
    protected $fillable = [
        'document_type',
        'signature_version', 'content_hash', 'signed_payload', 'signer_id', 'revoked_at', 'source_model', 'source_id',
        'document_id',
        'document_number',
        'verification_hash',
        'qr_payload',
        'issuer_name',
        'party_name',
        'party_email',
        'party_phone',
        'total_amount',
        'tax_amount',
        'currency',
        'issue_date',
        'due_date',
        'status',
        'signatory_name',
        'signatory_title',
        'signature_data',
        'lipa_namba',
        'tin_number',
        'vrn_number',
        'bank_details',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'verified_at' => 'datetime',
        'signed_payload' => 'encrypted:array',
        'revoked_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
