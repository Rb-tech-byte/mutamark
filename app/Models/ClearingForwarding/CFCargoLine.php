<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFCargoLine extends Model
{
    use HasFactory;

    protected $table = 'cf_cargo_lines';

    protected $fillable = [
        'cf_job_id',
        'commodity_description',
        'package_type',
        'package_count',
        'gross_weight_kg',
        'net_weight_kg',
        'cbm',
        'marks_numbers',
        'hs_code',
        'customs_value',
        'currency',
    ];

    protected $casts = [
        'package_count' => 'decimal:2',
        'gross_weight_kg' => 'decimal:3',
        'net_weight_kg' => 'decimal:3',
        'cbm' => 'decimal:3',
        'customs_value' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function classification()
    {
        return $this->hasOne(CFClassification::class, 'cf_cargo_line_id');
    }
}

