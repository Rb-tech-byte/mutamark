<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreeWayMatchConfig extends Model
{
    use HasFactory;

    protected $table = 'three_way_match_configs';

    protected $fillable = [
        'qty_tolerance_percent',
        'price_tolerance_percent',
        'max_variance_amount_tzs',
        'require_qc_passed',
        'auto_hold_on_variance',
        'created_by',
    ];

    protected $casts = [
        'qty_tolerance_percent' => 'decimal:2',
        'price_tolerance_percent' => 'decimal:2',
        'max_variance_amount_tzs' => 'decimal:2',
        'require_qc_passed' => 'boolean',
        'auto_hold_on_variance' => 'boolean',
    ];

    /**
     * Get or create company configuration.
     */
    public static function getForCompany(int $companyId): self
    {
        return self::firstOrCreate(
            ['created_by' => $companyId],
            [
                'qty_tolerance_percent' => 0.00,
                'price_tolerance_percent' => 0.00,
                'max_variance_amount_tzs' => 0.00,
                'require_qc_passed' => true,
                'auto_hold_on_variance' => true,
            ]
        );
    }
}
