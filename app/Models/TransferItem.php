<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    use HasFactory;

    protected $table = 'transfer_items';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'requested_quantity',
        'dispatched_quantity',
        'received_quantity',
        'discrepancy_quantity',
        'from_bin_id',
        'to_bin_id',
        'unit_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:4',
            'dispatched_quantity' => 'decimal:4',
            'received_quantity' => 'decimal:4',
            'discrepancy_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(\Workdo\ProductService\Models\ProductServiceItem::class, 'product_id');
    }

    public function fromBin()
    {
        return $this->belongsTo(WarehouseLocation::class, 'from_bin_id');
    }

    public function toBin()
    {
        return $this->belongsTo(WarehouseLocation::class, 'to_bin_id');
    }
}
