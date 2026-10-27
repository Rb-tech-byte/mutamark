<?php

namespace Workdo\Bookings\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingsDuration extends Model
{
    use HasFactory;

    protected $table = 'bookings_duration';
    protected $fillable = [
        'item_id',
        'duration',
        'workspace',
        'created_by',
    ];
 
}
