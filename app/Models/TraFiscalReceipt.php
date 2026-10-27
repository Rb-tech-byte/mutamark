<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraFiscalReceipt extends Model
{
    use HasFactory;

    protected $table = 'tra_fiscal_receipts';

    protected $fillable = [
        'receipt_number',
        'source_type',
        'source_id',
        'invoice_number',
        'customer_name',
        'customer_tin',
        'customer_vrn',
        'total_tax_exclusive_amount',
        'tax_amount_a',
        'tax_amount_b',
        'tax_amount_c',
        'tax_amount_d',
        'total_tax_inclusive_amount',
        'z_number',
        'daily_receipt_counter',
        'rct_verification_code',
        'qr_payload',
        'vfd_device_serial',
        'status',
        'fiscalized_at',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_tax_exclusive_amount' => 'decimal:2',
            'tax_amount_a' => 'decimal:2',
            'tax_amount_b' => 'decimal:2',
            'tax_amount_c' => 'decimal:2',
            'tax_amount_d' => 'decimal:2',
            'total_tax_inclusive_amount' => 'decimal:2',
            'fiscalized_at' => 'datetime',
            'daily_receipt_counter' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->receipt_number)) {
                $year = date('Y');
                $last = static::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = $last ? ((int)substr($last->receipt_number, -5)) + 1 : 1;
                $model->receipt_number = sprintf("TRA-RCT-%s-%05d", $year, $seq);
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'source_id');
    }

    public function pos()
    {
        return $this->belongsTo(\Workdo\Pos\Models\Pos::class, 'source_id');
    }

    public function zReport()
    {
        return $this->belongsTo(TraZReport::class, 'z_number', 'z_report_number');
    }

    public function verification()
    {
        return $this->hasOne(DocumentVerification::class, 'document_id')
            ->where('document_type', 'tra_fiscal_receipt');
    }
}
