<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileServiceCustomPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'slug',
        'description',
        'contents',
        'is_editable',
        'enable_page_footer',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_editable' => 'boolean'
    ];
}