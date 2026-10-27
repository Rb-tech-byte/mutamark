<?php

namespace Workdo\Bookings\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Workdo\ProductService\Entities\ProductService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingsStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'staff',
        'service',
        'created_by',
        'workspace',
    ];

    public function staffs()
    {
        return $this->hasOne(User::class, 'id', 'staff');
    }

    public function services()
    {
        if($this->service)
        {
            return ProductService::whereIn('id', explode(',', $this->service))->pluck('name');
        }
        return collect();

    }
 
}
