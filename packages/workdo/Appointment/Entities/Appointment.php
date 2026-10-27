<?php

namespace Modules\Appointment\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'question',
        'appointment_type',
        'date',
        'week_day',
        'start_time',
        'end_time',
        'meeting_type',
        'enable_field',
        'is_enabled',
        'workspace',
        'created_by'
    ];

    protected static function newFactory()
    {
        return \Modules\Appointment\Database\factories\AppointmentFactory::new();
    }

    public static $appointment_type = [
        'free' => 'Free',
        'paid' => 'Paid'
    ];

    public static $week_day = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    public function creatorName()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
