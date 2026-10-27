<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number','po_date','expected_delivery_date','vendor_id','warehouse_id','requisition_id','rfq_id','subtotal','tax_amount','discount_amount','total_amount','currency','status','approved_by','approved_at','delivery_terms','payment_terms','notes','creator_id','created_by','supplier_accepted_at'];
    protected $casts = ['po_date' => 'date', 'approved_at' => 'datetime', 'supplier_accepted_at' => 'datetime'];
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function vendor() { return $this->belongsTo(\Workdo\Account\Models\Vendor::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
