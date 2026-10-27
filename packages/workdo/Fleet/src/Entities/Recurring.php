<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recurring extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'workspace',
        'created_by',
    ];
}
