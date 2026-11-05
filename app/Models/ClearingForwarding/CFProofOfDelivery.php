<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFProofOfDelivery extends Model
{
    use HasFactory;

    protected $table = 'cf_proof_of_deliveries';

    protected $fillable = [
        'cf_delivery_id',
        'cf_job_id',
        'recipient_name',
        'recipient_title',
        'recipient_id_type',
        'recipient_id_number',
        'recipient_phone',
        'signature_data',
        'signed_at',
        'photo_evidence_paths',
        'official_stamp_received',
        'verification_hash',
        'verified_by',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'photo_evidence_paths' => 'array',
        'official_stamp_received' => 'boolean',
    ];

    public function delivery()
    {
        return $this->belongsTo(CFDelivery::class, 'cf_delivery_id');
    }

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

