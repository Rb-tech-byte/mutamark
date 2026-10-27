<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_number',
        'requisition_date',
        'required_by_date',
        'department_name',
        'warehouse_id',
        'requester_id',
        'priority',
        'status',
        'total_estimated_amount',
        'purpose',
        'budget_reference',
        'approved_by',
        'approved_at',
        'approval_notes',
        'preferred_vendor_id',
        'converted_invoice_id',
        'converted_po_id',
        'verification_hash',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'requisition_date' => 'date',
        'required_by_date' => 'date',
        'approved_at' => 'datetime',
        'total_estimated_amount' => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
        'priority_label',
        'department',
        'estimated_total_cost',
        'justification',
        'rejection_reason',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class, 'requisition_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function preferredVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preferred_vendor_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'converted_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Aliases
    public function getDepartmentAttribute(): string
    {
        return $this->department_name ?? 'Operations';
    }

    public function getEstimatedTotalCostAttribute(): float
    {
        return (float) $this->total_estimated_amount;
    }

    public function getJustificationAttribute(): ?string
    {
        return $this->purpose;
    }

    public function getRejectionReasonAttribute(): ?string
    {
        return $this->approval_notes;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted (Pending Approval)',
            'approved' => 'Approved (Ready for PO)',
            'rejected' => 'Rejected',
            'converted_to_rfq' => 'Converted to RFQ',
            'converted_to_po', 'converted' => 'Converted to Purchase Bill',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'Draft')),
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => ucfirst($this->priority ?? 'Medium'),
        };
    }
}
