<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFTransportOrder extends Model
{
    use HasFactory;

    protected $table = 'cf_transport_orders';

    protected $fillable = [
        'cf_job_id',
        'order_number',
        'transporter_type',
        'transporter_vendor_id',
        'vehicle_id',
        'driver_id',
        'trailer_number',
        'carrier_name',
        'driver_name',
        'driver_phone',
        'driver_license',
        'vehicle_plate',
        'pickup_location',
        'delivery_destination',
        'route_corridor',
        'cargo_summary',
        'agreed_freight_cost',
        'currency',
        'dispatch_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'waybill_file_path',
    ];

    protected $casts = [
        'agreed_freight_cost' => 'decimal:2',
        'dispatch_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function transporter()
    {
        return $this->belongsTo(User::class, 'transporter_vendor_id');
    }

    public function vehicle()
    {
        if (class_exists(\Workdo\Fleet\Entities\Vehicle::class)) {
            return $this->belongsTo(\Workdo\Fleet\Entities\Vehicle::class, 'vehicle_id');
        }
        return null;
    }

    public function driver()
    {
        if (class_exists(\Workdo\Fleet\Entities\Driver::class)) {
            return $this->belongsTo(\Workdo\Fleet\Entities\Driver::class, 'driver_id');
        }
        return null;
    }

    public function trackingEvents()
    {
        return $this->hasMany(CFTrackingEvent::class, 'cf_transport_order_id')->orderBy('event_time', 'asc');
    }

    public function deliveries()
    {
        return $this->hasMany(CFDelivery::class, 'cf_transport_order_id');
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $prefix = "TO-{$year}-";
        $latest = static::where('order_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->order_number, strlen($prefix));
            $nextSeq = str_pad($lastSeq + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '00001';
        }

        return $prefix . $nextSeq;
    }
}

