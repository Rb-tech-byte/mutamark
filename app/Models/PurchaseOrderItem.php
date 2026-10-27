<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id','product_id','product_name','quantity','unit_price','tax_rate','tax_amount','total_amount'];
    protected $casts = ['quantity_ordered' => 'decimal:4', 'quantity_received'=>'decimal:4', 'unit_price' => 'decimal:2', 'tax_rate' => 'decimal:2'];
    protected $appends = ['quantity'];
    public function getQuantityAttribute() { return $this->quantity_ordered; }
    public function setQuantityAttribute($value): void { $this->attributes['quantity_ordered']=$value; }
}
