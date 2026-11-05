<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFDisbursement extends Model
{
    use HasFactory;

    protected $table = 'cf_disbursements';

    protected $fillable = [
        'cf_job_id',
        'payee_name',
        'payee_vendor_id',
        'charge_category',
        'description',
        'payment_method',
        'disbursed_amount',
        'currency',
        'amount_tzs',
        'payment_reference',
        'receipt_voucher_number',
        'receipt_file_path',
        'payment_date',
        'is_reconciled',
        'authorized_by',
    ];

    protected $casts = [
        'disbursed_amount' => 'decimal:2',
        'amount_tzs' => 'decimal:2',
        'payment_date' => 'date',
        'is_reconciled' => 'boolean',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'payee_vendor_id');
    }

    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}

