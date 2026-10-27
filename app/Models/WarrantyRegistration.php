<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_code',
        'product_id',
        'serial_number',
        'invoice_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'start_date',
        'end_date',
        'warranty_period_months',
        'warranty_terms',
        'status',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public static function generateWarrantyCode(): string
    {
        $year = date('Y');
        $prefix = "WAR-{$year}-";
        $latest = self::where('warranty_code', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->warranty_code, -5);
            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }

        return "{$prefix}{$nextNumber}";
    }

    public function serviceTickets()
    {
        return $this->hasMany(ServiceTicket::class, 'warranty_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }
}
