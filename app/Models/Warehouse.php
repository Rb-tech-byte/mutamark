<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'warehouse_type',
        'address',
        'city',
        'zip_code',
        'phone',
        'email',
        'manager_name',
        'storage_capacity_cbm',
        'temperature_controlled',
        'is_active',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'temperature_controlled' => 'boolean',
            'storage_capacity_cbm' => 'decimal:2',
        ];
    }

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id');
    }

    public function stocks()
    {
        return $this->hasMany(\Workdo\ProductService\Models\WarehouseStock::class, 'warehouse_id');
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(Transfer::class, 'from_warehouse');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(Transfer::class, 'to_warehouse');
    }

    public function adjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'warehouse_id');
    }
}
