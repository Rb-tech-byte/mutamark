<?php

namespace Modules\Appointment\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentCallback extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'unique_id',
        'user_id',
        'appointment_id',
        'reason',
        'date',
        'start_time',
        'end_time',
        'start_url',
        'join_url',
        'workspace',
        'created_by'
    ];

    protected static function newFactory()
    {
        return \Modules\Appointment\Database\factories\AppointmentCallbackFactory::new();
    }

    public function appointment()
    {
        return $this->hasOne('Modules\Appointment\Entities\Appointment', 'id', 'appointment_id');
    }

    public function schedule()
    {
        return $this->hasOne('Modules\Appointment\Entities\Schedule', 'id', 'schedule_id');
    }

    public function creatorName()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
