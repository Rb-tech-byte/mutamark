<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFTrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'cf_tracking_events';

    protected $fillable = [
        'cf_job_id',
        'cf_transport_order_id',
        'milestone_code',
        'location_name',
        'latitude',
        'longitude',
        'event_time',
        'remarks',
        'photo_evidence_path',
        'is_customer_visible',
        'recorded_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'event_time' => 'datetime',
        'is_customer_visible' => 'boolean',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function transportOrder()
    {
        return $this->belongsTo(CFTransportOrder::class, 'cf_transport_order_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

