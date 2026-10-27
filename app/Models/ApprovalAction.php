<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_request_id',
        'tier_level',
        'actor_id',
        'action',
        'previous_status',
        'new_status',
        'comments',
        'ip_address',
        'digital_signature',
    ];

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
