<?php

namespace Modules\FileSharing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'file_path',
        'ip_address',
        'details',
        'date',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\FileSharing\Database\factories\FileDownloadFactory::new();
    }
}
