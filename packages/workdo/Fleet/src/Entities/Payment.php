<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pay_amount',
        'description',
        'booking_id',
        'workspace',
        'created_by',
    ];

    public static function paymentSummary($payments)
    {
        $total = 0;

        foreach($payments as $payment)
        {
            $total += $payment->pay_amount;
        }

        return $total;
    }
}
