<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workdo\ProductService\Entities\ProductService;

class Logbook extends Model
{
    use HasFactory;

    protected $table = 'fleet_logbooks';
    protected $fillable = [
        'driver_name',
        'vehicle_name',
        'start_date',
        'end_date',
        'start_odometer',
        'end_odometer',
        'rate',
        'total_distance',
        'total_price',
        'notes',
        'workspace',
        'created_by',
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class, 'id', 'driver_name')->with('client');
    }

    public function VehicleType()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_name');
    }

    public function item_rate()
    {
        return $this->hasOne(ProductService::class, 'id', 'rate');
    }


}
