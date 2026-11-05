<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFValuation extends Model
{
    use HasFactory;

    protected $table = 'cf_valuations';

    protected $fillable = [
        'cf_job_id',
        'invoice_value_fob',
        'freight_charges',
        'insurance_charges',
        'other_additions',
        'customs_value_cif_foreign',
        'currency',
        'tra_exchange_rate',
        'customs_value_tzs',
        'import_duty_tzs',
        'vat_tzs',
        'railway_levy_tzs',
        'customs_processing_fee_tzs',
        'other_statutory_taxes_tzs',
        'total_statutory_taxes_tzs',
        'reviewed_by',
    ];

    protected $casts = [
        'invoice_value_fob' => 'decimal:2',
        'freight_charges' => 'decimal:2',
        'insurance_charges' => 'decimal:2',
        'other_additions' => 'decimal:2',
        'customs_value_cif_foreign' => 'decimal:2',
        'tra_exchange_rate' => 'decimal:4',
        'customs_value_tzs' => 'decimal:2',
        'import_duty_tzs' => 'decimal:2',
        'vat_tzs' => 'decimal:2',
        'railway_levy_tzs' => 'decimal:2',
        'customs_processing_fee_tzs' => 'decimal:2',
        'other_statutory_taxes_tzs' => 'decimal:2',
        'total_statutory_taxes_tzs' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

