<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverAttechment extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'file_name',
        'file_path',
        'file_size',
        'file_status',
        'created_by',
        'workspace'
    ];

}
