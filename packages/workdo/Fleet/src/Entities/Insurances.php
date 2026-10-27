<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insurances extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_provider',
        'vehicle_name',
        'start_date',
        'end_date',
        'scheduled_date',
        'scheduled_period',
        'deductible',
        'charge_payable',
        'policy_number',
        'policy_document',
        'notes',
        'workspace',
        'created_by',
    ];

    public function VehicleName()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_name');
    }

    public function Recurring()
    {
        return $this->hasOne(Recurring::class, 'id', 'scheduled_period');
    }

}
