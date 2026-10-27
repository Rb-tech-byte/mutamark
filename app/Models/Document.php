<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'document_number','title','category','entity_type','entity_id',
        'file_path','file_name','file_type','file_size','description',
        'tags','access_level','expiry_date','is_confidential',
        'creator_id','created_by',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date'    => 'date',
            'is_confidential'=> 'boolean',
            'file_size'      => 'integer',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public static function generateNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'DOC-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
