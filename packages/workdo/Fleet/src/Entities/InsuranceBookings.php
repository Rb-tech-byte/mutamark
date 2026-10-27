<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InsuranceBookings extends Model
{
    use HasFactory;

    protected $table = "insurance_booking";
    protected $fillable = [
        'insurance_id',
        'start_date',
        'end_date',
        'amount',
        'workspace',
        'created_by',
    ];
}
