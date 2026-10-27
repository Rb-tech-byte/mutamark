<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'delivery_date',
        'estimated_delivery_date',
        'actual_delivery_date',
        'invoice_id',
        'sales_order_id',
        'customer_id',
        'warehouse_id',
        'vehicle_id',
        'driver_id',
        'driver_name',
        'driver_phone',
        'vehicle_plate',
        'shipping_address',
        'destination_city',
        'contact_person',
        'contact_phone',
        'status',
        'tracking_number',
        'total_packages',
        'total_weight_kg',
        'notes',
        'proof_of_delivery_recipient',
        'proof_of_delivery_signature',
        'proof_of_delivery_notes',
        'delivery_verified_at',
        'verification_hash',
        'stock_deducted',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'delivery_verified_at' => 'datetime',
        'stock_deducted' => 'boolean',
        'total_weight_kg' => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
        'total_units_shipped',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class, 'delivery_note_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Fleet\Entities\Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Fleet\Entities\Driver::class, 'driver_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'scheduled' => 'Scheduled',
            'dispatched' => 'Dispatched',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered (POD Verified)',
            'returned' => 'Returned',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status ?? 'Draft'),
        };
    }

    public function getTotalUnitsShippedAttribute(): float
    {
        return (float) $this->items->sum('quantity_shipped');
    }
}
