<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFContainer extends Model
{
    use HasFactory;

    protected $table = 'cf_containers';

    protected $fillable = [
        'cf_job_id',
        'container_number',
        'size_type',
        'seal_number',
        'shipping_line',
        'free_days',
        'discharge_date',
        'gate_out_date',
        'demurrage_start_date',
        'empty_return_deadline',
        'empty_returned_at',
        'empty_depot_name',
        'status',
        'interchange_receipt_number',
    ];

    protected $casts = [
        'discharge_date' => 'date',
        'gate_out_date' => 'date',
        'demurrage_start_date' => 'date',
        'empty_return_deadline' => 'date',
        'empty_returned_at' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    /**
     * Calculate days remaining until demurrage accrual begins
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->empty_returned_at) {
            return null; // Already returned
        }
        if (!$this->empty_return_deadline) {
            return null;
        }

        return now()->diffInDays($this->empty_return_deadline, false);
    }
}

