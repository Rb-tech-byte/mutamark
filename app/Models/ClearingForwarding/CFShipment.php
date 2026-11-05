<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFShipment extends Model
{
    use HasFactory;

    protected $table = 'cf_shipments';

    protected $fillable = [
        'cf_job_id',
        'bl_awb_number',
        'vessel_flight_truck',
        'voyage_number',
        'shipping_line_airline',
        'shipping_agent_id',
        'origin_country',
        'port_of_loading',
        'port_of_discharge',
        'terminal_icd',
        'final_destination',
        'etd',
        'atd',
        'eta',
        'ata',
        'manifest_rotation_number',
    ];

    protected $casts = [
        'etd' => 'date',
        'atd' => 'date',
        'eta' => 'date',
        'ata' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }
}

