<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposal_number',
        'asset_id',
        'disposal_date',
        'disposal_type',
        'sale_amount',
        'book_value_at_disposal',
        'gain_loss_amount',
        'reason',
        'approved_by',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'sale_amount' => 'decimal:2',
        'book_value_at_disposal' => 'decimal:2',
        'gain_loss_amount' => 'decimal:2',
    ];

    public static function generateDisposalNumber(): string
    {
        $year = date('Y');
        $prefix = "DSP-{$year}-";
        $latest = self::where('disposal_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->disposal_number, -5);
            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }

        return "{$prefix}{$nextNumber}";
    }

    public function asset()
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }
}
