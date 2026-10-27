<?php

namespace Modules\Appointment\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'user_id',
        'name',
        'email',
        'phone',
        'date',
        'start_time',
        'end_time',
        'appointment_id',
        'questions',
        'meeting_type',
        'start_url',
        'join_url',
        'cancel_description',
        'status',
        'send_feedback',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\Appointment\Database\factories\ScheduleFactory::new();
    }

    public function appointment()
    {
        return $this->hasOne('Modules\Appointment\Entities\Appointment', 'id', 'appointment_id');
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
