<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'asset_id',
        'transfer_date',
        'from_location',
        'to_location',
        'from_custodian_id',
        'to_custodian_id',
        'reason',
        'approved_by',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public static function generateTransferNumber(): string
    {
        $year = date('Y');
        $prefix = "TRF-{$year}-";
        $latest = self::where('transfer_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->transfer_number, -5);
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

    public function fromCustodian()
    {
        return $this->belongsTo(User::class, 'from_custodian_id');
    }

    public function toCustodian()
    {
        return $this->belongsTo(User::class, 'to_custodian_id');
    }
}
