<?php

namespace Workdo\Fleet\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'lincese_number',
        'lincese_type',
        'expiry_date',
        'join_date',
        'address',
        'dob',
        'Working_time',
        'driver_status',
        'workspace',
        'created_by',
    ];

    public function client()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function lincese()
    {
        return $this->hasOne(License::class, 'id', 'lincese_type');
    }
}
