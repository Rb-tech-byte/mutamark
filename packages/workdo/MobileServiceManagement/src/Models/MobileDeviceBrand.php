<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MobileDeviceBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'creator_id',
        'created_by',
    ];

}