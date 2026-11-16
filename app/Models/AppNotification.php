<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'severity',
        'link',
        'dedupe_key',
        'data',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}

