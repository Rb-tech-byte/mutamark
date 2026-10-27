<?php

namespace Workdo\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class VendorPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'payment_date',
        'vendor_id',
        'bank_account_id',
        'reference_number',
        'payment_amount',
        'wht_base_amount', 'wht_rate', 'wht_amount',
        'status',
        'notes',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2'
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(VendorPaymentAllocation::class, 'payment_id');
    }

    public function debitNoteApplications(): HasMany
    {
        return $this->hasMany(DebitNoteApplication::class, 'payment_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = static::generatePaymentNumber();
            }
        });
    }

    public static function generatePaymentNumber(): string
    {
        return \App\Services\DocumentNumberService::next('PV');
    }
}
