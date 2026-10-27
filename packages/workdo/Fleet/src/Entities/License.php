<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model
{
    use HasFactory;

    protected $table = 'driver_permit_types';

    protected $fillable = [
        'name',
        'workspace',
        'created_by',
    ];

}
