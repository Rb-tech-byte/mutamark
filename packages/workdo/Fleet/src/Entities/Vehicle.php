<?php

namespace Workdo\Fleet\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',
        'vehicle_type',
        'fuel_type',
        'registration_date',
        'register_ex_date',
        'lincense_plate',
        'vehical_id_num',
        'model_year',
        'driver_name',
        'seat_capacity',
        'status',
        'workspace',
        'created_by',
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class, 'id', 'driver_name')->with('client');
    }

    public function driver_phone()
    {
        return $this->hasOne(Driver::class, 'user_id', 'driver_name');
    }
    public function VehicleType()
    {
        return $this->hasOne(VehicleType::class, 'id', 'vehicle_type');
    }

    public function FuelType()
    {
        return $this->hasOne(FuelType::class, 'id', 'fuel_type');
    }
}
