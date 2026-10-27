<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'name',
        'asset_category_id',
        'purchase_date',
        'purchase_cost',
        'salvage_value',
        'current_value',
        'accumulated_depreciation',
        'depreciation_method',
        'depreciation_rate',
        'useful_life_years',
        'location',
        'custodian_id',
        'supplier_id',
        'serial_number',
        'warranty_expiry_date',
        'status',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
    ];

    public static function generateAssetCode(): string
    {
        $year = date('Y');
        $prefix = "AST-{$year}-";
        $latest = self::where('asset_code', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->asset_code, -5);
            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }

        return "{$prefix}{$nextNumber}";
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function custodian()
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }

    public function transfers()
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public function disposal()
    {
        return $this->hasOne(AssetDisposal::class, 'asset_id');
    }

    /**
     * Compute next monthly depreciation in TZS
     */
    public function computeMonthlyDepreciation(): float
    {
        $cost = (float) $this->purchase_cost;
        $salvage = (float) $this->salvage_value;
        $current = (float) $this->current_value;
        $rate = (float) $this->depreciation_rate / 100.0;
        $method = $this->depreciation_method;

        if ($method === 'none' || $current <= $salvage) {
            return 0.0;
        }

        if ($method === 'reducing_balance') {
            // Annual rate applied to current book value, divided by 12 months
            $monthly = ($current * $rate) / 12.0;
        } else {
            // Straight-line: (cost - salvage) * rate / 12 months
            $depreciableBase = max(0, $cost - $salvage);
            $monthly = ($depreciableBase * $rate) / 12.0;
        }

        // Do not depreciate below salvage value
        if (($current - $monthly) < $salvage) {
            $monthly = max(0, $current - $salvage);
        }

        return round($monthly, 2);
    }
}
