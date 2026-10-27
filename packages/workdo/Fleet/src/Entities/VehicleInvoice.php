<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleInvoice extends Model
{
    use HasFactory;

    protected $table='vehicle_invoice';
    
    protected $fillable = [
        'id',
        'invoice_id',
        'product_type',
        'item',
        'start_location',
        'end_location',
        'trip_type',
        'rate',
        'start_date',
        'end_date',
        'description',
        'distance',
    ];

}
