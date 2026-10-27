<?php

namespace Workdo\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class CustomerPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'payment_date',
        'customer_id',
        'bank_account_id',
        'reference_number',
        'payment_amount',
        'status',
        'notes',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class, 'payment_id');
    }

    public function creditNoteApplications(): HasMany
    {
        return $this->hasMany(CreditNoteApplication::class, 'payment_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customerPayment) {
            if (empty($customerPayment->payment_number)) {
                $customerPayment->payment_number = static::generatePaymentNumber();
            }
        });
    }

    public static function generatePaymentNumber(): string
    {
        return \App\Services\DocumentNumberService::next('RV');
    }
}
