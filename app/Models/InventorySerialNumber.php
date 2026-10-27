<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workdo\ProductService\Models\ProductServiceItem;

class InventorySerialNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'batch_number',
        'product_id',
        'warehouse_id',
        'status',
        'purchase_cost',
        'selling_price',
        'warranty_months',
        'warranty_expiry_date',
        'barcode',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'warranty_months' => 'integer',
            'warranty_expiry_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Scope query by company created_by
     */
    public function scopeForCompany($query, $userId = null)
    {
        return $query->where('created_by', $userId ?? creatorId());
    }

    /**
     * Scope for in stock units
     */
    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }
}
