<?php

namespace Modules\Contract\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\Contract\Database\factories\ContractTypeFactory::new();
    }
}
