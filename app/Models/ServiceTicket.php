<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'warranty_id',
        'customer_id',
        'customer_name',
        'contact_phone',
        'product_name',
        'serial_number',
        'service_type',
        'issue_reported',
        'diagnostic_findings',
        'vendor_repair',
        'vendor_name',
        'vendor_tracking_ref',
        'estimated_cost',
        'actual_cost',
        'assigned_technician_id',
        'status',
        'completed_at',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'vendor_repair' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $prefix = "SRV-{$year}-";
        $latest = self::where('ticket_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->ticket_number, -5);
            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }

        return "{$prefix}{$nextNumber}";
    }

    public function warranty()
    {
        return $this->belongsTo(WarrantyRegistration::class, 'warranty_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }
}
