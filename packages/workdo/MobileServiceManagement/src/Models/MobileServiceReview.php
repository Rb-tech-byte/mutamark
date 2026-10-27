<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workdo\ProductService\Models\ProductServiceItem;

class MobileServiceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile_service_id',
        'name',
        'email',
        'rating',
        'review',
        'created_by'
    ];

    public function mobileService()
    {
        return $this->belongsTo(ProductServiceItem::class, 'mobile_service_id');
    }
}