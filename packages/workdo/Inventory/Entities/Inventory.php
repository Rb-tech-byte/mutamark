<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'description',
        'feild_id',
        'workspace',
        'created_by'
    ];

    protected static function newFactory()
    {
        return \Modules\Inventory\Database\factories\InventoryFactory::new();
    }
}
