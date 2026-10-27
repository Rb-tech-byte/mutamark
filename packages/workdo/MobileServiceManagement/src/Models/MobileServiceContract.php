<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MobileServiceContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'contract_type',
        'start_date',
        'end_date',
        'contract_details',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date'
        ];
    }

}