<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workdo\ProductService\Models\ProductServiceItem;

class PurchaseRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'product_id',
        'product_name',
        'sku',
        'description',
        'quantity',
        'estimated_unit_price',
        'estimated_total_price',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total_price' => 'decimal:2',
    ];

    protected $appends = [
        'quantity_requested',
        'estimated_unit_cost',
        'estimated_total_cost',
        'specifications',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'requisition_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }

    // Aliases for seamless interoperability
    public function getQuantityRequestedAttribute(): float
    {
        return (float) $this->quantity;
    }

    public function getEstimatedUnitCostAttribute(): float
    {
        return (float) $this->estimated_unit_price;
    }

    public function getEstimatedTotalCostAttribute(): float
    {
        return (float) $this->estimated_total_price;
    }

    public function getSpecificationsAttribute(): ?string
    {
        return $this->description;
    }
}
