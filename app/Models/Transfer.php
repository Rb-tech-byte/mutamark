<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workdo\ProductService\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_warehouse',
        'to_warehouse',
        'product_id',
        'quantity',
        'date',
        'status',
        'driver_name',
        'vehicle_number',
        'shipping_carrier',
        'expected_delivery_date',
        'dispatched_at',
        'received_at',
        'received_by',
        'transit_notes',
        'inspection_notes',
        'total_items_count',
        'total_transferred_qty',
        'qr_verification_code',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'total_transferred_qty' => 'decimal:2',
            'date' => 'date',
            'expected_delivery_date' => 'date',
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
            'total_items_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->transfer_number)) {
                $year = date('Y');
                $lastTransfer = static::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = $lastTransfer ? ((int)substr($lastTransfer->transfer_number, -5)) + 1 : 1;
                $model->transfer_number = sprintf("STO-%s-%05d", $year, $seq);
            }
            if (empty($model->status)) {
                $model->status = 'completed';
            }
            if (empty($model->total_transferred_qty) && !empty($model->quantity)) {
                $model->total_transferred_qty = $model->quantity;
            }
        });
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse');
    }

    public function product()
    {
        return $this->belongsTo(\Workdo\ProductService\Models\ProductServiceItem::class, 'product_id');
    }

    public function items()
    {
        return $this->hasMany(TransferItem::class, 'transfer_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function verification()
    {
        return $this->hasOne(DocumentVerification::class, 'document_id')
            ->where('document_type', 'stock_transfer');
    }

    /**
     * Dispatch the transfer order from source warehouse
     */
    public function dispatchOrder(array $metadata = []): bool
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['transfers', 'transfer_items', 'warehouse_stocks']);
        return DB::transaction(function () use ($metadata) {
        $locked = static::lockForUpdate()->findOrFail($this->id);
        $this->setRawAttributes($locked->getAttributes(), true); $this->unsetRelations();
        if ($this->status !== 'draft') {
            return false;
        }

        // Deduct items from source warehouse
        if ($this->items()->count() > 0) {
            foreach ($this->items as $item) {
                $stock = WarehouseStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $this->from_warehouse)
                    ->lockForUpdate()->first();
                if (!$stock || (float) $stock->quantity - $stock->reservedQuantity() < (float) $item->dispatched_quantity) {
                    throw ValidationException::withMessages(['stock' => __('Insufficient stock for this transfer.')]);
                }
                if ($stock) {
                    $stock->quantity -= $item->dispatched_quantity;
                    $stock->save();
                }
            }
        } elseif ($this->product_id && $this->quantity > 0) {
            $stock = WarehouseStock::where('product_id', $this->product_id)
                ->where('warehouse_id', $this->from_warehouse)
                ->lockForUpdate()->first();
            if (!$stock || (float) $stock->quantity - $stock->reservedQuantity() < (float) $this->quantity) {
                throw ValidationException::withMessages(['stock' => __('Insufficient stock for this transfer.')]);
            }
            if ($stock) {
                $stock->quantity -= $this->quantity;
                $stock->save();
            }
        }

        $this->status = 'in_transit';
        $this->dispatched_at = now();
        if (!empty($metadata['driver_name'])) $this->driver_name = $metadata['driver_name'];
        if (!empty($metadata['vehicle_number'])) $this->vehicle_number = $metadata['vehicle_number'];
        if (!empty($metadata['shipping_carrier'])) $this->shipping_carrier = $metadata['shipping_carrier'];
        if (!empty($metadata['transit_notes'])) $this->transit_notes = $metadata['transit_notes'];
        $this->save();

        return true;
        }, 3);
    }

    /**
     * Receive and inspect stock at destination warehouse
     */
    public function receiveOrder(array $inspections = [], ?string $notes = null, ?int $userId = null): bool
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['transfers', 'transfer_items', 'warehouse_stocks', 'document_verifications']);
        return DB::transaction(function () use ($inspections, $notes, $userId) {
        $locked = static::lockForUpdate()->findOrFail($this->id);
        $this->setRawAttributes($locked->getAttributes(), true); $this->unsetRelations();
        if ($this->status !== 'in_transit') {
            return false;
        }

        $totalReceived = 0;

        if ($this->items()->count() > 0) {
            foreach ($this->items as $item) {
                $recQty = isset($inspections[$item->id]) 
                    ? (float)$inspections[$item->id] 
                    : (float)$item->dispatched_quantity;
                if ($recQty < 0 || $recQty > (float) $item->dispatched_quantity) {
                    throw ValidationException::withMessages(['inspections' => __('Received quantities must be between zero and dispatched quantities.')]);
                }
                
                $item->received_quantity = $recQty;
                $item->discrepancy_quantity = $item->dispatched_quantity - $recQty;
                $item->save();

                $totalReceived += $recQty;

                // Add to destination warehouse stock
                $destStock = WarehouseStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $this->to_warehouse,
                    ],
                    ['quantity' => 0]
                );
                $destStock = WarehouseStock::lockForUpdate()->findOrFail($destStock->id);
                $destStock->quantity += $recQty;
                $destStock->save();
            }
        } elseif ($this->product_id && $this->quantity > 0) {
            $destStock = WarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->product_id,
                    'warehouse_id' => $this->to_warehouse,
                ],
                ['quantity' => 0]
            );
            $destStock = WarehouseStock::lockForUpdate()->findOrFail($destStock->id);
            $destStock->quantity += $this->quantity;
            $destStock->save();
            $totalReceived = $this->quantity;
        }

        $this->status = 'received';
        $this->received_at = now();
        $this->received_by = $userId ?: auth()->id();
        if ($notes) {
            $this->inspection_notes = $notes;
        }
        $this->save();

        // Generate Cryptographic Verification Seal
        $this->generateVerificationSeal();

        return true;
        }, 3);
    }

    /**
     * Generate Cryptographic SHA-256 seal
     */
    public function generateVerificationSeal(): DocumentVerification
    {
        $payload = json_encode([
            'document_type' => 'stock_transfer',
            'transfer_number' => $this->transfer_number,
            'from_warehouse_id' => $this->from_warehouse,
            'to_warehouse_id' => $this->to_warehouse,
            'total_items_count' => $this->total_items_count,
            'total_transferred_qty' => $this->total_transferred_qty,
            'dispatched_at' => $this->dispatched_at?->toIso8601String(),
            'received_at' => $this->received_at?->toIso8601String(),
            'created_by' => $this->created_by,
        ]);

        $hash = hash('sha256', $payload . config('app.key'));
        $qrPayload = url("/verify/document/{$hash}");

        $verification = DocumentVerification::updateOrCreate(
            [
                'document_type' => 'stock_transfer',
                'document_id' => $this->id,
            ],
            [
                'document_number' => $this->transfer_number,
                'verification_hash' => $hash,
                'qr_payload' => $qrPayload,
                'issuer_name' => 'MUTAMARK ENTERPRISES',
                'party_name' => 'Branch Transfer: ' . ($this->toWarehouse?->name ?? 'Destination Warehouse'),
                'total_amount' => 0.00,
                'tax_amount' => 0.00,
                'currency' => 'TZS',
                'issue_date' => $this->transfer_date ?? now()->toDateString(),
                'status' => 'verified',
                'tin_number' => '123-456-789',
                'vrn_number' => '40012345T',
                'verified_at' => now(),
                'created_by' => $this->creator_id ?? $this->created_by,
            ]
        );

        $this->qr_verification_code = $hash;
        $this->saveQuietly();

        return $verification;
    }
}
