<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workdo\ProductService\Models\ProductServiceItem;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'product_id',
        'ordered_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'unit_price',
        'total_amount',
        'batch_number',
        'rejection_reason'
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'accepted_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }
}
