<?php

namespace Workdo\MobileServiceManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workdo\ProductService\Models\ProductServiceItem;

class MobileServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'customer_name',
        'email',
        'mobile_no',
        'address',
        'priority',
        'mobile_model',
        'description',
        'special_requests',
        'attachment',
        'is_approve',
        'tracking_status',
        'tracking_status_log',
        'is_technician_asign',
        'is_repair_end',
        'payment_status',
        'creator_id',
        'created_by',
        'mobile_service_id',
        'device_brand',
    ];

    protected $casts = [
        'tracking_status_log' => 'array',
    ];


    public function mobileService()
    {
        return $this->belongsTo(ProductServiceItem::class, 'mobile_service_id');
    }
    public function getServiceCreatedName()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function trackingStatus()
    {
        return $this->belongsTo(MobileTrackingStatus::class, 'tracking_status');
    }

    public function deviceBrand()
    {
        return $this->belongsTo(MobileDeviceBrand::class, 'device_brand');
    }

    public function assignedTechnicians()
    {
        return $this->hasMany(MobileServiceAssignTechnician::class, 'mobile_service_id');
    }
}