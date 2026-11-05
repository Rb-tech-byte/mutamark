<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFChargeEstimate extends Model
{
    use HasFactory;

    protected $table = 'cf_charge_estimates';

    protected $fillable = [
        'cf_job_id',
        'charge_category',
        'charge_name',
        'is_disbursement',
        'estimated_amount',
        'currency',
        'exchange_rate',
        'amount_tzs',
        'notes',
    ];

    protected $casts = [
        'is_disbursement' => 'boolean',
        'estimated_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_tzs' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }
}

