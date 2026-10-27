<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    protected $table = 'timesheets';

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'date',
        'hours',
        'minutes',
        'hourly_rate',
        'total_cost',
        'type',
        'status',
        'notes',
        'workspace_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'integer',
        'minutes' => 'integer',
        'hourly_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Taskly\Models\Project::class, 'project_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Taskly\Models\ProjectTask::class, 'task_id');
    }
}
