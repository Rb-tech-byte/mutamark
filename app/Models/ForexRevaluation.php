<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\ChartOfAccount;
use Workdo\Account\Models\JournalEntry;

class ForexRevaluation extends Model
{
    use HasFactory;

    protected $table = 'forex_revaluations';

    protected $fillable = [
        'revaluation_number',
        'revaluation_date',
        'currency_code',
        'market_exchange_rate',
        'book_exchange_rate',
        'foreign_currency_balance',
        'book_value_base',
        'revalued_value_base',
        'unrealized_gain_loss',
        'status',
        'journal_entry_id',
        'reversal_journal_entry_id',
        'bank_account_id',
        'gl_account_id',
        'notes',
        'verification_seal',
        'revalued_by',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'revaluation_date' => 'date',
            'market_exchange_rate' => 'decimal:6',
            'book_exchange_rate' => 'decimal:6',
            'foreign_currency_balance' => 'decimal:2',
            'book_value_base' => 'decimal:2',
            'revalued_value_base' => 'decimal:2',
            'unrealized_gain_loss' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->revaluation_number)) {
                $year = date('Y');
                $last = static::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = $last ? (((int) substr($last->revaluation_number, -5)) + 1) : 1;
                $model->revaluation_number = sprintf('REV-%s-%05d', $year, $seq);
            }
        });
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function reversalJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_journal_entry_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'gl_account_id');
    }

    public function revaluer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revalued_by');
    }

    /**
     * Generate or update cryptographic document verification seal for this revaluation.
     */
    public function generateVerificationSeal(): DocumentVerification
    {
        $payload = json_encode([
            'document_type' => 'forex_revaluation',
            'revaluation_number' => $this->revaluation_number,
            'date' => $this->revaluation_date?->toDateString(),
            'currency' => $this->currency_code,
            'unrealized_gain_loss' => $this->unrealized_gain_loss,
            'market_rate' => $this->market_exchange_rate,
            'journal_entry_id' => $this->journal_entry_id,
            'company_tin' => '123-456-789',
        ]);

        $hash = hash('sha256', $payload . config('app.key'));
        $qrPayload = url("/verify/document/{$hash}");

        $verification = DocumentVerification::updateOrCreate(
            [
                'document_type' => 'forex_revaluation',
                'document_id' => $this->id,
            ],
            [
                'document_number' => $this->revaluation_number,
                'verification_hash' => $hash,
                'qr_payload' => $qrPayload,
                'issuer_name' => 'MUTAMARK ENTERPRISES LIMITED',
                'party_name' => 'Bank of Tanzania (BoT) Market Revaluation',
                'party_email' => 'treasury@mutamark.co.tz',
                'party_phone' => '+255 22 211 0000',
                'total_amount' => abs($this->unrealized_gain_loss),
                'tax_amount' => 0.00,
                'currency' => 'TZS',
                'issue_date' => $this->revaluation_date ? $this->revaluation_date->toDateString() : now()->toDateString(),
                'status' => 'verified',
                'tin_number' => '123-456-789',
                'vrn_number' => '40012345T',
                'verified_at' => now(),
                'created_by' => $this->creator_id,
            ]
        );

        $this->verification_seal = $hash;
        $this->saveQuietly();

        return $verification;
    }
}
