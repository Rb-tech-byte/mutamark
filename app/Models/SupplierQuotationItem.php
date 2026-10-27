<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierQuotationItem extends Model
{
    use HasFactory;

    protected $table = 'supplier_quotation_items';

    protected $fillable = [
        'supplier_quotation_id',
        'rfq_item_id',
        'unit_price',
        'quantity',
        'total_price',
        'remarks',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SupplierQuotation::class, 'supplier_quotation_id');
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(RfqItem::class, 'rfq_item_id');
    }
}
