<?php

namespace Workdo\Fleet\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'vehicle_name',
        'driver_name',
        'trip_type',
        'start_date',
        'end_date',
        'start_address',
        'end_address',
        'total_price',
        'status',
        'notes',
        'workspace',
        'created_by',
    ];

    public function BookingUser()
    {
        return $this->hasOne(User::class, 'id', 'customer_name');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_name');
    }

    public function driver()
    {
        return $this->hasOne(Driver::class, 'id', 'driver_name');
    }

    public static function getBookingSummary($bookings)
    {
        $total = 0;

        foreach($bookings as $booking)
        {
            $total += $booking->value;
        }

        return $total;
    }
}
