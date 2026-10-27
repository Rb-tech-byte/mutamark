<?php

namespace Workdo\Bookings\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingsExtraService extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'status',
        'workspace',
        'created_by'
    ];
 
}
