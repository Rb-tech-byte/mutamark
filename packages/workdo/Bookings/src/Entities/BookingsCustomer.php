<?php

namespace Workdo\Bookings\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingsCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'number',
        'email',
        'client_id',
        'customer',
        'created_by',
        'workspace',
    ];

    public function client()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }
}
