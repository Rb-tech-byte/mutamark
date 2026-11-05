<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFDelivery extends Model
{
    use HasFactory;

    protected $table = 'cf_deliveries';

    protected $fillable = [
        'cf_job_id',
        'cf_transport_order_id',
        'delivery_number',
        'delivery_date',
        'delivery_address',
        'packages_delivered',
        'cargo_condition',
        'condition_notes',
        'delivery_note_file_path',
        'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'packages_delivered' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function transportOrder()
    {
        return $this->belongsTo(CFTransportOrder::class, 'cf_transport_order_id');
    }

    public function proofOfDelivery()
    {
        return $this->hasOne(CFProofOfDelivery::class, 'cf_delivery_id');
    }

    public static function generateDeliveryNumber(): string
    {
        $year = date('Y');
        $prefix = "DN-CF-{$year}-";
        $latest = static::where('delivery_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->delivery_number, strlen($prefix));
            $nextSeq = str_pad($lastSeq + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '00001';
        }

        return $prefix . $nextSeq;
    }
}

