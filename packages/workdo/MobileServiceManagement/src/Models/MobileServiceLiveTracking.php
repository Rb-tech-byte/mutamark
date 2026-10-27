<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileServiceLiveTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'tracking_status_id',
        'date',
        'creator_id',
        'created_by'
    ];
}
