<?php

namespace Workdo\ProductService\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    public function reservedQuantity(?int $exceptOrderId = null): float
    {
        return (float) \Illuminate\Support\Facades\DB::table('sales_order_items as items')->join('sales_orders as orders', 'orders.id', '=', 'items.sales_order_id')
            ->where('orders.warehouse_id', $this->warehouse_id)->where('items.product_id', $this->product_id)
            ->where('orders.stock_reserved', true)->whereNotIn('orders.status', ['cancelled','fulfilled'])
            ->when($exceptOrderId, fn ($query) => $query->where('orders.id', '!=', $exceptOrderId))
            ->sum(\Illuminate\Support\Facades\DB::raw('items.quantity - items.fulfilled_quantity'));
    }

    protected $fillable = [
        'product_id',
        'warehouse_id', 
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'warehouse_id');
    }
}
