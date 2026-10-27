<?php

namespace Modules\FileSharing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_path',
        'file_size',
        'file_status',
        'auto_destroy',
        'filesharing_type',
        'email',
        'is_pass_enable',
        'password',
        'total_downloads',
        'description',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\FileSharing\Database\factories\FileShareFactory::new();
    }
}
