<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFClassification extends Model
{
    use HasFactory;

    protected $table = 'cf_classifications';

    protected $fillable = [
        'cf_job_id',
        'cf_cargo_line_id',
        'proposed_hs_code',
        'approved_hs_code',
        'tariff_description',
        'duty_rate_percentage',
        'vat_rate_percentage',
        'excise_rate_percentage',
        'classification_rationale',
        'wco_general_rule',
        'reviewed_by',
        'status',
    ];

    protected $casts = [
        'duty_rate_percentage' => 'decimal:2',
        'vat_rate_percentage' => 'decimal:2',
        'excise_rate_percentage' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function cargoLine()
    {
        return $this->belongsTo(CFCargoLine::class, 'cf_cargo_line_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

