<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workdo\Account\Models\BankAccount;

class MobileServicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'payment_date',
        'service_price',
        'repair_charge',
        'payment_amount',
        'extra_charges',
        'bank_account_id',
        'payment_status',
        'tax_amount',
        'description',
        'receipt',
        'mobile_service_id',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'service_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'repair_charge' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'extra_charges' => 'array',
    ];

    public function mobileServiceRequest()
    {
        return $this->belongsTo(MobileServiceRequest::class, 'mobile_service_id');
    }

    public function trackingStatus()
    {
        return $this->belongsTo(MobileTrackingStatus::class);
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
        $year = date('Y');
        $month = date('m');
        $lastPayment = static::where('payment_number', 'like', "MSP-{$year}-{$month}-%")
            ->where('created_by', creatorId())
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return "MSP-{$year}-{$month}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
