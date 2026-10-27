<?php

namespace Workdo\MobileServiceManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileServiceAssignTechnician extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile_service_id',
        'technician_id',
        'completion_date',
        'creator_id',
        'created_by',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function mobileServiceRequest()
    {
        return $this->belongsTo(MobileServiceRequest::class, 'mobile_service_id');
    }
}