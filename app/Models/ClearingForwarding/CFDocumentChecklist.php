<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFDocumentChecklist extends Model
{
    use HasFactory;

    protected $table = 'cf_document_checklists';

    protected $fillable = [
        'cf_job_id',
        'document_type',
        'document_name',
        'is_mandatory',
        'is_original_received',
        'document_reference',
        'file_path',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_original_received' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

