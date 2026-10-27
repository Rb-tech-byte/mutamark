<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MobileTrackingStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon_name',
        'status_color',
        'status_name',
        'order',
        'creator_id',
        'created_by',
    ];

}