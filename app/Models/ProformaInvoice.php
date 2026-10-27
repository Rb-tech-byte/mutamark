<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProformaInvoice extends Model
{
    protected $fillable = [
        'proforma_number','issue_date','expiry_date','customer_id','customer_name',
        'customer_email','customer_phone','customer_address','currency','subtotal',
        'tax_amount','discount_amount','total_amount','status','converted_to_invoice_id',
        'payment_terms','notes','verification_hash','creator_id','created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'expiry_date' => 'date',
            'subtotal'    => 'float',
            'tax_amount'  => 'float',
            'discount_amount' => 'float',
            'total_amount' => 'float',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProformaInvoiceItem::class);
    }

    public static function generateNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'PF-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
