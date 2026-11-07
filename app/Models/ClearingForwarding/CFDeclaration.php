<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFDeclaration extends Model
{
    use HasFactory;

    protected $table = 'cf_declarations';

    protected $fillable = [
        'cf_job_id',
        'customs_office',
        'declaration_type',
        'declaration_number',
        'tancis_reference',
        'regime_code',
        'declarant_user_id',
        'submission_date',
        'assessment_number',
        'assessment_date',
        'assessed_amount_tzs',
        'payment_control_number',
        'payment_date',
        'release_order_number',
        'release_date',
        'status',
        'customs_channel',

        // TANCIS Manual Addendum fields
        'worksheet_version',
        'worksheet_generated_at',
        'worksheet_generated_by',
        'manual_entry_source',
        'external_submission_time',
        'lodgement_evidence_path',
        'import_duty_assessed_tzs',
        'vat_assessed_tzs',
        'rdl_assessed_tzs',
        'cpf_assessed_tzs',
        'excise_assessed_tzs',
        'other_taxes_assessed_tzs',
        'penalties_tzs',
        'assessment_document_path',
        'is_assessment_verified',
        'assessment_verified_by',
        'assessment_verified_at',
        'payment_receipt_path',
        'is_payment_verified',
        'payment_verified_by',
        'payment_verified_at',
        'release_order_path',
        'is_release_verified',
        'release_verified_by',
        'release_verified_at',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'assessment_date' => 'datetime',
        'payment_date' => 'datetime',
        'release_date' => 'datetime',
        'worksheet_generated_at' => 'datetime',
        'external_submission_time' => 'datetime',
        'assessment_verified_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'release_verified_at' => 'datetime',
        'assessed_amount_tzs' => 'decimal:2',
        'import_duty_assessed_tzs' => 'decimal:2',
        'vat_assessed_tzs' => 'decimal:2',
        'rdl_assessed_tzs' => 'decimal:2',
        'cpf_assessed_tzs' => 'decimal:2',
        'excise_assessed_tzs' => 'decimal:2',
        'other_taxes_assessed_tzs' => 'decimal:2',
        'penalties_tzs' => 'decimal:2',
        'is_assessment_verified' => 'boolean',
        'is_payment_verified' => 'boolean',
        'is_release_verified' => 'boolean',
        'worksheet_version' => 'integer',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function declarant()
    {
        return $this->belongsTo(User::class, 'declarant_user_id');
    }

    public function worksheetGenerator()
    {
        return $this->belongsTo(User::class, 'worksheet_generated_by');
    }

    public function assessmentVerifier()
    {
        return $this->belongsTo(User::class, 'assessment_verified_by');
    }

    public function paymentVerifier()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function releaseVerifier()
    {
        return $this->belongsTo(User::class, 'release_verified_by');
    }

    public function events()
    {
        return $this->hasMany(CFCustomsEvent::class, 'cf_declaration_id')->orderBy('event_time', 'desc');
    }
}
