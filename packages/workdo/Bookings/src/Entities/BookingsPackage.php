<?php

namespace Workdo\Bookings\Entities;

use Illuminate\Database\Eloquent\Model;
use Workdo\Bookings\Entities\BookingsExtraService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workdo\ProductService\Entities\ProductService;

class BookingsPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'item',
        'services',
        'delivery_time',
        'delivery_period',
        'price',
        'workspace',
        'created_by'
    ];

    public static $periods = [
        'Hours' => 'Hours',
        'Days' => 'Days',
        'Weeks' => 'Weeks',
        'Month' => 'Month',
        'Years' => 'Years',
    ];

    public function items()
    {
        return $this->hasOne(ProductService::class, 'id', 'item');
    }
    public function extra_service()
    {
        if($this->services)
        {
            return BookingsExtraService::whereIn('id', explode(',', $this->services))->pluck('name');
        }
        return collect();

    }
 
}
