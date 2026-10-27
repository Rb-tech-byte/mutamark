<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalDelegation extends Model
{
    protected $fillable = ['delegator_id', 'delegate_id', 'start_date', 'end_date', 'document_types', 'reason', 'is_active', 'created_by'];
    protected $casts = ['is_active' => 'boolean', 'start_date' => 'date', 'end_date' => 'date'];

    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }
}
