<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'purchase_order_id',
        'invoice_date',
        'due_date',
        'vendor_id',
        'warehouse_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'debit_note_applied',
        'balance_amount',
        'status',
        'payment_terms',
        'notes',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debit_note_applied' => 'decimal:2',
        'balance_amount' => 'decimal:2'
    ];

    protected $appends = ['display_status'];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'invoice_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function vendorDetails(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Account\Models\Vendor::class, 'vendor_id', 'user_id');
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(\Workdo\Account\Models\VendorPaymentAllocation::class, 'invoice_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'original_invoice_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'overdue';
        }
        return $this->status;
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($invoice) { if ($invoice->status !== 'draft') throw new \LogicException('Finalized invoices require debit notes or reversals.'); });
        static::updating(function ($invoice) {
            if ($invoice->getRawOriginal('status') !== 'draft' && $invoice->isDirty(['vendor_id','warehouse_id','invoice_date','subtotal','discount_amount','tax_amount','total_amount'])) throw new \LogicException('Finalized invoice content is immutable.');
        });

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        return \App\Services\DocumentNumberService::next('PINV');
    }
}
