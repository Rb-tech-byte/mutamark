<?php

namespace Modules\Contract\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'user_id',
        'note',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\Contract\Database\factories\ContractNoteFactory::new();
    }
}
