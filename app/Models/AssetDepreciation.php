<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'fiscal_year',
        'period',
        'opening_value',
        'depreciation_amount',
        'closing_value',
        'journal_entry_id',
        'posted_by',
        'posted_at',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'opening_value' => 'decimal:2',
        'depreciation_amount' => 'decimal:2',
        'closing_value' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }
}
