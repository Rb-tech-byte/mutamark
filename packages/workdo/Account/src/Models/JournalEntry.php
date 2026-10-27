<?php

namespace Workdo\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_number',
        'journal_date',
        'entry_type',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'journal_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function isBalanced(): bool
    {
        return $this->total_debit == $this->total_credit;
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journalEntry) {
            \App\Services\FinancialControlService::assertPeriodOpen($journalEntry->journal_date, $journalEntry->created_by);
            if (empty($journalEntry->journal_number)) {
                $journalEntry->journal_number = static::generateJournalNumber();
            }
        });
        static::deleting(function ($journal) { if ($journal->status !== 'draft') throw new \LogicException('Posted journals require reversals.'); });
        static::updating(function ($journal) { if ($journal->getRawOriginal('status') !== 'draft' && $journal->isDirty(['journal_date','description','total_debit','total_credit','reference_type','reference_id'])) throw new \LogicException('Posted journal content is immutable.'); });
    }

    public static function generateJournalNumber(): string
    {
        return \App\Services\DocumentNumberService::next('JV');
    }
}
