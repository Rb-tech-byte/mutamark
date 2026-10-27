<?php

namespace Workdo\Bookings\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingsBusinessHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_name',
        'start_time',
        'end_time',
        'day_off',
        'break_hours',
        'workspace_id',
        'created_by'
    ];

    public static $weekdays = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public static function dayWiseData($day)
    {
        return BookingsBusinessHours::where('created_by',creatorId())->where('workspace_id',getActiveWorkSpace())->where('day_name',$day)->first();
    }

}
