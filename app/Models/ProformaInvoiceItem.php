<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaInvoiceItem extends Model
{
    protected $fillable = [
        'proforma_invoice_id','product_id','product_name','sku','unit',
        'quantity','unit_price','tax_rate','tax_amount','discount_amount',
        'total_amount','description',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'float',
            'unit_price'      => 'float',
            'tax_rate'        => 'float',
            'tax_amount'      => 'float',
            'discount_amount' => 'float',
            'total_amount'    => 'float',
        ];
    }

    public function proforma()
    {
        return $this->belongsTo(ProformaInvoice::class);
    }
}
