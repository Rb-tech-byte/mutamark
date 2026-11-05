<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFPermit extends Model
{
    use HasFactory;

    protected $table = 'cf_permits';

    protected $fillable = [
        'cf_job_id',
        'agency',
        'permit_type',
        'application_number',
        'permit_number',
        'issue_date',
        'expiry_date',
        'status',
        'special_conditions',
        'certificate_file',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }
}

