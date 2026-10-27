<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'depreciation_method',
        'depreciation_rate',
        'useful_life_years',
        'creator_id',
        'created_by',
    ];

    public function assets()
    {
        return $this->hasMany(FixedAsset::class, 'asset_category_id');
    }
}
