<?php

namespace Modules\Appointment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'question_type',
        'available_answer',
        'is_required',
        'is_enabled',
        'workspace',
        'created_by'
    ];

    protected static function newFactory()
    {
        return \Modules\Appointment\Database\factories\QuestionFactory::new();
    }

    public static $question_type = [
        'radio' => 'Radio',
        'dropdown' => 'Dropdown',
        'text' => 'Text',
        'checkbox' => 'Checkbox',
    ];
}
