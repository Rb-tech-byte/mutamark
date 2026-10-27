<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workdo\ProductService\Models\ProductServiceItem;

class DeliveryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'product_id',
        'product_name',
        'sku',
        'quantity_ordered',
        'quantity_shipped',
        'quantity_received',
        'unit',
        'batch_number',
        'serial_number',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_shipped' => 'decimal:2',
        'quantity_received' => 'decimal:2',
    ];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'delivery_note_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }
}
