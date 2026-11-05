<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFDeclaration extends Model
{
    use HasFactory;

    protected $table = 'cf_declarations';

    protected $fillable = [
        'cf_job_id',
        'customs_office',
        'declaration_type',
        'declaration_number',
        'tancis_reference',
        'regime_code',
        'declarant_user_id',
        'submission_date',
        'assessment_number',
        'assessment_date',
        'assessed_amount_tzs',
        'payment_control_number',
        'payment_date',
        'release_order_number',
        'release_date',
        'status',
        'customs_channel',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'assessment_date' => 'datetime',
        'payment_date' => 'datetime',
        'release_date' => 'datetime',
        'assessed_amount_tzs' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function declarant()
    {
        return $this->belongsTo(User::class, 'declarant_user_id');
    }

    public function events()
    {
        return $this->hasMany(CFCustomsEvent::class, 'cf_declaration_id')->orderBy('event_time', 'desc');
    }
}

