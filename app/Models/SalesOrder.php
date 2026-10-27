<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'proposal_id',
        'customer_id',
        'warehouse_id',
        'order_date',
        'delivery_due_date',
        'status',
        'stock_reserved',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_terms',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_due_date' => 'date',
        'stock_reserved' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customerDetails(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Account\Models\Customer::class, 'customer_id', 'user_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(SalesProposal::class, 'proposal_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        return \App\Services\DocumentNumberService::next('SO');
    }

    public function reserve(bool $reserve = true): void
    {
        \App\Services\CompanyAccess::check($this);
        \Illuminate\Support\Facades\DB::transaction(function () use ($reserve) {
            $order = static::where('created_by', creatorId())->lockForUpdate()->findOrFail($this->id);
            abort_if(in_array($order->status, ['fulfilled','cancelled']), 409, 'Finalized orders cannot change reservations.');
            if ($reserve) foreach ($order->items->groupBy('product_id')->sortKeys() as $productId => $items) {
                $stock = \Workdo\ProductService\Models\WarehouseStock::where('warehouse_id', $order->warehouse_id)->where('product_id', $productId)->lockForUpdate()->first();
                $required = (float) $items->sum('quantity') - (float) $items->sum('fulfilled_quantity');
                if (!$stock || (float) $stock->quantity - $stock->reservedQuantity($order->id) < $required) throw \Illuminate\Validation\ValidationException::withMessages(['stock' => 'Insufficient unreserved stock for this order.']);
            }
            $order->update(['stock_reserved' => $reserve, 'status' => $reserve ? 'stock_reserved' : 'confirmed']);
        }, 3);
    }
}
