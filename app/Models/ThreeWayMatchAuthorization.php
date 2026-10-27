<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreeWayMatchAuthorization extends Model
{
    use HasFactory;

    protected $table = 'three_way_match_authorizations';

    protected $fillable = [
        'grn_id',
        'purchase_invoice_id',
        'authorization_status',
        'qty_variance_detected',
        'price_variance_detected',
        'total_variance_amount_tzs',
        'disbursement_cleared',
        'cleared_by',
        'cleared_at',
        'clearance_notes',
        'verification_hash',
        'created_by',
    ];

    protected $casts = [
        'qty_variance_detected' => 'decimal:2',
        'price_variance_detected' => 'decimal:2',
        'total_variance_amount_tzs' => 'decimal:2',
        'disbursement_cleared' => 'boolean',
        'cleared_at' => 'datetime',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function clearedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
