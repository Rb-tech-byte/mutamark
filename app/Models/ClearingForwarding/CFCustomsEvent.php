<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFCustomsEvent extends Model
{
    use HasFactory;

    protected $table = 'cf_customs_events';

    protected $fillable = [
        'cf_declaration_id',
        'cf_job_id',
        'event_type',
        'officer_name',
        'event_time',
        'location',
        'instructions',
        'findings',
        'outcome',
        'attachment_path',
        'recorded_by',
    ];

    protected $casts = [
        'event_time' => 'datetime',
    ];

    public function declaration()
    {
        return $this->belongsTo(CFDeclaration::class, 'cf_declaration_id');
    }

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

