<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CFBorderMovement extends Model
{
    protected $table = 'cf_border_movements';

    protected $fillable = [
        'movement_number',
        'cf_job_id',
        'corridor',
        'truck_registration',
        'trailer_number',
        'driver_name',
        'cargo_type',
        'movement_type',
        'origin_country',
        'destination_country',
        'border_entry_at',
        'border_exit_at',
        'customs_reference',
        'rotation_number',
        'transit_doc_number',
        'gate_pass_number',
        'clearance_status',
        'delay_reason',
        'border_expenses',
        'currency',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'border_entry_at' => 'datetime',
        'border_exit_at' => 'datetime',
        'border_expenses' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }
}

