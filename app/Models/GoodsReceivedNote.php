<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workdo\Account\Models\Vendor;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class GoodsReceivedNote extends Model
{
    protected $fillable = [
        'grn_number',
        'purchase_order_number',
        'purchase_order_id',
        'purchase_invoice_id',
        'vendor_id',
        'warehouse_id',
        'grn_date',
        'delivery_challan_number',
        'carrier_name',
        'vehicle_number',
        'received_by',
        'inspection_status',
        'matching_status',
        'total_ordered_qty',
        'total_received_qty',
        'total_accepted_qty',
        'total_rejected_qty',
        'total_amount',
        'quality_inspection_notes',
        'rejection_notes',
        'status',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'grn_date' => 'date',
        'total_ordered_qty' => 'decimal:2',
        'total_received_qty' => 'decimal:2',
        'total_accepted_qty' => 'decimal:2',
        'total_rejected_qty' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    /**
     * Compute 3-Way Match Comparison with linked Purchase Invoice / PO
     */
    public function computeThreeWayMatch(): array
    {
        $hasInvoice = !empty($this->purchase_invoice_id) && $this->purchaseInvoice;
        $itemsAnalysis = [];
        $totalQtyVariance = 0;
        $totalPriceVariance = 0;
        $matchStatus = 'matched';

        foreach ($this->items as $item) {
            $billedQty = 0;
            $billedPrice = 0;
            $billedTotal = 0;

            if ($hasInvoice) {
                $invItem = $this->purchaseInvoice->items
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($invItem) {
                    $billedQty = (float) $invItem->quantity;
                    $billedPrice = (float) $invItem->unit_price;
                    $billedTotal = (float) $invItem->total_amount;
                }
            }

            $orderedQty = (float) $item->ordered_quantity;
            $acceptedQty = (float) $item->accepted_quantity;
            $poPrice = (float) $item->unit_price;

            $qtyVariance = $hasInvoice ? abs($acceptedQty - $billedQty) : 0;
            $priceVariance = $hasInvoice ? abs($poPrice - $billedPrice) : 0;

            if ($qtyVariance > 0.001 || $priceVariance > 0.001) {
                $matchStatus = 'variance_detected';
            }

            $totalQtyVariance += $qtyVariance;
            $totalPriceVariance += $priceVariance;

            $itemsAnalysis[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : 'N/A',
                'sku' => $item->product ? $item->product->sku : 'N/A',
                'po_qty' => $orderedQty,
                'grn_qty' => $acceptedQty,
                'billed_qty' => $billedQty,
                'po_price' => $poPrice,
                'billed_price' => $billedPrice,
                'qty_variance' => $qtyVariance,
                'price_variance' => $priceVariance,
                'is_item_matched' => ($qtyVariance <= 0.001 && $priceVariance <= 0.001)
            ];
        }

        if (!$hasInvoice) {
            $matchStatus = 'unmatched';
        }

        return [
            'has_invoice' => $hasInvoice,
            'match_status' => $matchStatus,
            'total_qty_variance' => $totalQtyVariance,
            'total_price_variance' => $totalPriceVariance,
            'items' => $itemsAnalysis
        ];
    }
}
