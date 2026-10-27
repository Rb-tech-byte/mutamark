<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'adjustment_number',
        'warehouse_id',
        'adjustment_type',
        'status',
        'adjustment_date',
        'reason',
        'total_variance_value',
        'approved_by',
        'posted_at',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_date' => 'date',
            'posted_at' => 'datetime',
            'total_variance_value' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->adjustment_number)) {
                $year = date('Y');
                $lastAdj = static::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = $lastAdj ? ((int)substr($lastAdj->adjustment_number, -5)) + 1 : 1;
                $model->adjustment_number = sprintf("ADJ-%s-%05d", $year, $seq);
            }
        });
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class, 'adjustment_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
