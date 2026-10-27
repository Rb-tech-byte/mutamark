<?php

namespace Modules\CustomField\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'field_id',
        'value',
    ];

    protected static function newFactory()
    {
        return \Modules\CustomField\Database\factories\CustomFieldValueFactory::new();
    }
}
