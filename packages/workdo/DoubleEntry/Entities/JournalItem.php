<?php

namespace Modules\DoubleEntry\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Account\Entities\ChartOfAccount;

class JournalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal',
        'account',
        'description',
        'debit',
        'credit',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\DoubleEntry\Database\factories\JournalItemFactory::new();
    }

    public function accounts()
    {
        return $this->hasOne(ChartOfAccount::class, 'id', 'account');
    }
}
