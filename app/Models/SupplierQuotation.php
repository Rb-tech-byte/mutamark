<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workdo\Account\Models\Vendor;

class SupplierQuotation extends Model
{
    use HasFactory;

    protected $table = 'supplier_quotations';

    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'vendor_name',
        'quotation_reference',
        'submission_date',
        'valid_until',
        'delivery_lead_time_days',
        'warranty_months',
        'payment_terms',
        'total_bid_amount',
        'status',
        'evaluation_score',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'valid_until' => 'date',
        'total_bid_amount' => 'decimal:2',
        'evaluation_score' => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierQuotationItem::class, 'supplier_quotation_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'Submitted / Pending Review',
            'shortlisted' => 'Shortlisted',
            'awarded' => 'Awarded Winner',
            'rejected' => 'Not Selected',
            default => ucfirst($this->status),
        };
    }
}
