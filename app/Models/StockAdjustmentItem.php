<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustment_items';

    protected $fillable = [
        'adjustment_id',
        'product_id',
        'system_quantity',
        'counted_quantity',
        'difference_quantity',
        'unit_cost',
        'variance_value',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:4',
            'counted_quantity' => 'decimal:4',
            'difference_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:2',
            'variance_value' => 'decimal:2',
        ];
    }

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(\Workdo\ProductService\Models\ProductServiceItem::class, 'product_id');
    }
}
