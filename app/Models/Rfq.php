<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workdo\Account\Models\Vendor;

class Rfq extends Model
{
    use HasFactory;

    protected $table = 'rfqs';

    protected $fillable = [
        'rfq_number',
        'title',
        'requisition_id',
        'issue_date',
        'closing_date',
        'delivery_deadline',
        'warehouse_id',
        'status',
        'payment_terms',
        'delivery_terms',
        'awarded_vendor_id',
        'awarded_bid_id',
        'awarded_po_id',
        'evaluation_notes',
        'verification_hash',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'closing_date' => 'date',
        'delivery_deadline' => 'date',
    ];

    protected $appends = [
        'status_label',
        'bids_count',
        'lowest_bid_amount',
        'total_target_amount',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'requisition_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'rfq_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(SupplierQuotation::class, 'rfq_id');
    }

    public function awardedVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'awarded_vendor_id');
    }

    public function awardedPo(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'awarded_po_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'published' => 'Open for Bidding',
            'evaluating' => 'Under Evaluation',
            'awarded' => 'Awarded & PO Issued',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getBidsCountAttribute(): int
    {
        return $this->quotations()->count();
    }

    public function getLowestBidAmountAttribute(): ?float
    {
        $min = $this->quotations()->where('total_bid_amount', '>', 0)->min('total_bid_amount');
        return $min ? (float)$min : null;
    }

    public function getTotalTargetAmountAttribute(): float
    {
        return (float) $this->items()->sum(\DB::raw('quantity * target_unit_price'));
    }
}
