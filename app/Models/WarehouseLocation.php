<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $table = 'warehouse_locations';

    protected $fillable = [
        'warehouse_id',
        'zone',
        'aisle',
        'rack',
        'shelf',
        'bin_code',
        'max_capacity_units',
        'is_active',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_capacity_units' => 'integer',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
