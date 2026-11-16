<?php

namespace Workdo\Pos\Models;

use Illuminate\Database\Eloquent\Model;

class PosOfflineSyncLog extends Model
{
    protected $fillable = [
        'client_tx_id',
        'pos_id',
        'warehouse_id',
        'status',
        'message',
        'payload',
        'conflict_details',
        'synced_at',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'conflict_details' => 'array',
        'synced_at' => 'datetime',
    ];
}

