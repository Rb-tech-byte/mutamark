<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Model;

class CFCustomsRule extends Model
{
    protected $table = 'cf_customs_rules';

    protected $fillable = [
        'cpc_code',
        'procedure_name',
        'country_code',
        'import_duty_rate',
        'vat_rate',
        'excise_duty_rate',
        'rdl_rate',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'import_duty_rate' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'excise_duty_rate' => 'decimal:2',
        'rdl_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}

