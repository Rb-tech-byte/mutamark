<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MobileServicePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'description',
        'response_time',
        'resolution_time',
        'creator_id',
        'created_by',
    ];

}