<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CFFreightBooking extends Model
{
    protected $table = 'cf_freight_bookings';

    protected $fillable = [
        'booking_number',
        'cf_job_id',
        'freight_mode',
        'shipping_line_carrier',
        'vessel_flight_voyage',
        'port_of_loading',
        'port_of_discharge',
        'etd',
        'eta',
        'delivery_order_number',
        'freight_cost',
        'port_handling_charges',
        'free_days',
        'demurrage_days',
        'demurrage_amount',
        'detention_amount',
        'currency',
        'status',
        'shipping_instructions',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'etd' => 'date',
        'eta' => 'date',
        'freight_cost' => 'decimal:2',
        'port_handling_charges' => 'decimal:2',
        'demurrage_amount' => 'decimal:2',
        'detention_amount' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }
}

