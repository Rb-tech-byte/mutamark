<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraZReport extends Model
{
    use HasFactory;

    protected $table = 'tra_z_reports';

    protected $fillable = [
        'z_report_number',
        'report_date',
        'opening_receipt_number',
        'closing_receipt_number',
        'total_receipts_count',
        'gross_turnover',
        'net_taxable_turnover',
        'total_vat_18',
        'total_vat_zero_rated',
        'total_vat_exempt',
        'cumulative_turnover_to_date',
        'status',
        'tra_ack_token',
        'closed_at',
        'closed_by',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'closed_at' => 'datetime',
            'gross_turnover' => 'decimal:2',
            'net_taxable_turnover' => 'decimal:2',
            'total_vat_18' => 'decimal:2',
            'total_vat_zero_rated' => 'decimal:2',
            'total_vat_exempt' => 'decimal:2',
            'cumulative_turnover_to_date' => 'decimal:2',
            'total_receipts_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->z_report_number)) {
                $year = date('Y');
                $last = static::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = $last ? ((int)substr($last->z_report_number, -5)) + 1 : 1;
                $model->z_report_number = sprintf("ZREP-%s-%05d", $year, $seq);
            }
        });
    }

    public function receipts()
    {
        return $this->hasMany(TraFiscalReceipt::class, 'z_number', 'z_report_number');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
