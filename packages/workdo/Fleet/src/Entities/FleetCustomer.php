<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class FleetCustomer extends Model
{
    use HasFactory;

    protected $table = 'fleet_customers';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'workspace',
        'created_by',
    ];

    public function CustomerUser()
    {
        return FleetCustomer::where('id',$this->user_id)->first();

    }

    public function clients()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }
}
