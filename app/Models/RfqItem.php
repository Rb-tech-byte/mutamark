<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workdo\ProductService\Models\ProductServiceItem;

class RfqItem extends Model
{
    use HasFactory;

    protected $table = 'rfq_items';

    protected $fillable = [
        'rfq_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'quantity_required',
        'unit',
        'target_unit_price',
        'specifications',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_required' => 'decimal:2',
        'target_unit_price' => 'decimal:2',
    ];

    protected $appends = [
        'total_target_price',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (isset($model->quantity)) {
                $model->quantity_required = $model->quantity;
            } elseif (isset($model->quantity_required)) {
                $model->quantity = $model->quantity_required;
            }
        });
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(SupplierQuotationItem::class, 'rfq_item_id');
    }

    public function getTotalTargetPriceAttribute(): float
    {
        $qty = $this->quantity ?: $this->quantity_required ?: 0;
        return (float) ($qty * $this->target_unit_price);
    }
}
