<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workdo\Hrm\Models\Payroll;

class StatutoryDeclaration extends Model
{
    use HasFactory;

    protected $table = 'hrm_statutory_declarations';

    protected $fillable = [
        'payroll_id',
        'declaration_number',
        'statutory_type',
        'title',
        'filing_month',
        'due_date',
        'gepg_control_number',
        'total_taxable_gross',
        'employee_deduction_amount',
        'employer_contribution_amount',
        'penalty_amount',
        'total_payable_amount',
        'payment_status',
        'payment_date',
        'payment_method',
        'bank_reference',
        'acknowledged_at',
        'notes',
        'verification_seal',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'payment_date' => 'date',
            'acknowledged_at' => 'datetime',
            'total_taxable_gross' => 'decimal:2',
            'employee_deduction_amount' => 'decimal:2',
            'employer_contribution_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'total_payable_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->declaration_number)) {
                $year = date('Y');
                $last = static::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = $last ? (((int) substr($last->declaration_number, -5)) + 1) : 1;
                $model->declaration_number = sprintf('DEC-%s-%05d', $year, $seq);
            }
        });
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Generate or update cryptographic document verification seal for this declaration.
     */
    public function generateVerificationSeal(): DocumentVerification
    {
        $payload = json_encode([
            'document_type' => 'statutory_declaration',
            'declaration_number' => $this->declaration_number,
            'statutory_type' => $this->statutory_type,
            'filing_month' => $this->filing_month,
            'control_number' => $this->gepg_control_number,
            'total_payable' => $this->total_payable_amount,
            'payment_status' => $this->payment_status,
            'company_tin' => '123-456-789',
        ]);

        $hash = hash('sha256', $payload . config('app.key'));
        $qrPayload = url("/verify/document/{$hash}");

        $authorityName = match ($this->statutory_type) {
            'tra_paye' => 'Tanzania Revenue Authority (TRA)',
            'nssf_pension' => 'National Social Security Fund (NSSF)',
            'wcf_fund' => 'Workers Compensation Fund (WCF)',
            'sdl_levy' => 'TRA Skills Development Levy (SDL)',
            default => 'Tanzania Statutory Authorities',
        };

        $verification = DocumentVerification::updateOrCreate(
            [
                'document_type' => 'statutory_declaration',
                'document_id' => $this->id,
            ],
            [
                'document_number' => $this->declaration_number,
                'verification_hash' => $hash,
                'qr_payload' => $qrPayload,
                'issuer_name' => 'MUTAMARK ENTERPRISES LIMITED',
                'party_name' => $authorityName,
                'party_email' => 'compliance@mutamark.co.tz',
                'party_phone' => '+255 22 211 0000',
                'total_amount' => $this->total_payable_amount,
                'tax_amount' => $this->employee_deduction_amount,
                'currency' => 'TZS',
                'issue_date' => now()->toDateString(),
                'due_date' => $this->due_date ? $this->due_date->toDateString() : null,
                'status' => $this->payment_status === 'paid' ? 'verified' : 'pending',
                'lipa_namba' => $this->gepg_control_number ?: '16381432',
                'tin_number' => '123-456-789',
                'vrn_number' => '40012345T',
                'verified_at' => $this->payment_status === 'paid' ? now() : null,
                'created_by' => $this->creator_id,
            ]
        );

        $this->verification_seal = $hash;
        $this->saveQuietly();

        return $verification;
    }
}
