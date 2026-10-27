<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workdo\ProductService\Models\ProductServiceItem;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'fulfilled_quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'total_amount',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'fulfilled_quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }
}