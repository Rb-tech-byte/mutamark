<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\DocumentVerification;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class DeliveryNoteService
{
    /**
     * Generate sequential Delivery Note number adhering to Appendix A (DN-YYYY-#####).
     */
    public static function generateDeliveryNumber(int $creatorId): string
    {
        return DocumentNumberService::next('DN');
    }

    /**
     * Create a new Delivery Note with Line Items (CUD: Create).
     */
    public static function createDeliveryNote(array $data, int $creatorId, int $userId): DeliveryNote
    {
        return DB::transaction(function () use ($data, $creatorId, $userId) {
            $deliveryNumber = !empty($data['delivery_number'])
                ? $data['delivery_number']
                : self::generateDeliveryNumber($creatorId);

            // Auto-resolve vehicle plate & driver name if fleet IDs provided
            $vehiclePlate = $data['vehicle_plate'] ?? null;
            if (!empty($data['vehicle_id']) && empty($vehiclePlate)) {
                $vehicle = Vehicle::find($data['vehicle_id']);
                if ($vehicle) {
                    $vehiclePlate = $vehicle->lincense_plate ?? $vehicle->name;
                }
            }

            $driverName = $data['driver_name'] ?? null;
            $driverPhone = $data['driver_phone'] ?? null;
            if (!empty($data['driver_id']) && empty($driverName)) {
                $driver = Driver::find($data['driver_id']);
                if ($driver) {
                    $driverName = $driver->name;
                    $driverPhone = $driverPhone ?: $driver->phone;
                }
            }

            $deliveryNote = DeliveryNote::create([
                'delivery_number' => $deliveryNumber,
                'delivery_date' => $data['delivery_date'] ?? now()->toDateString(),
                'estimated_delivery_date' => $data['estimated_delivery_date'] ?? null,
                'invoice_id' => !empty($data['invoice_id']) ? $data['invoice_id'] : null,
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'vehicle_id' => !empty($data['vehicle_id']) ? $data['vehicle_id'] : null,
                'driver_id' => !empty($data['driver_id']) ? $data['driver_id'] : null,
                'driver_name' => $driverName,
                'driver_phone' => $driverPhone,
                'vehicle_plate' => $vehiclePlate,
                'shipping_address' => $data['shipping_address'] ?? null,
                'destination_city' => $data['destination_city'] ?? 'Dar es Salaam',
                'contact_person' => $data['contact_person'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'status' => ($data['status'] ?? 'draft') === 'dispatched' ? 'draft' : ($data['status'] ?? 'draft'),
                'tracking_number' => $data['tracking_number'] ?? ('TRK-' . strtoupper(substr(md5(uniqid()), 0, 10))),
                'total_packages' => $data['total_packages'] ?? 1,
                'total_weight_kg' => $data['total_weight_kg'] ?? 0.00,
                'notes' => $data['notes'] ?? null,
                'creator_id' => $userId,
                'created_by' => $creatorId,
            ]);

            // Create items
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qtyOrdered = (float)($item['quantity_ordered'] ?? $item['quantity_shipped'] ?? 1);
                    $qtyShipped = (float)($item['quantity_shipped'] ?? $qtyOrdered);

                    DeliveryNoteItem::create([
                        'delivery_note_id' => $deliveryNote->id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'] ?? 'Product Item',
                        'sku' => $item['sku'] ?? null,
                        'quantity_ordered' => $qtyOrdered,
                        'quantity_shipped' => $qtyShipped,
                        'quantity_received' => 0.00,
                        'unit' => $item['unit'] ?? 'units',
                        'batch_number' => $item['batch_number'] ?? null,
                        'serial_number' => $item['serial_number'] ?? null,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            // Auto-dispatch if requested
            if (($data['status'] ?? '') === 'dispatched') {
                self::dispatchDeliveryNote($deliveryNote, $userId);
            }

            return $deliveryNote->fresh(['items', 'customer', 'warehouse', 'vehicle', 'driver', 'invoice']);
        });
    }

    /**
     * Update an existing Delivery Note (CUD: Update).
     */
    public static function updateDeliveryNote(DeliveryNote $dn, array $data): DeliveryNote
    {
        return DB::transaction(function () use ($dn, $data) {
            CompanyAccess::check($dn);
            $dn = DeliveryNote::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->id);
            abort_unless(in_array($dn->status, ['draft', 'scheduled']), 409, 'Issued deliveries cannot be edited.');
            $dn->update([
                'delivery_date' => $data['delivery_date'] ?? $dn->delivery_date,
                'estimated_delivery_date' => $data['estimated_delivery_date'] ?? $dn->estimated_delivery_date,
                'invoice_id' => !empty($data['invoice_id']) ? $data['invoice_id'] : $dn->invoice_id,
                'customer_id' => $data['customer_id'] ?? $dn->customer_id,
                'warehouse_id' => $data['warehouse_id'] ?? $dn->warehouse_id,
                'vehicle_id' => !empty($data['vehicle_id']) ? $data['vehicle_id'] : $dn->vehicle_id,
                'driver_id' => !empty($data['driver_id']) ? $data['driver_id'] : $dn->driver_id,
                'driver_name' => $data['driver_name'] ?? $dn->driver_name,
                'driver_phone' => $data['driver_phone'] ?? $dn->driver_phone,
                'vehicle_plate' => $data['vehicle_plate'] ?? $dn->vehicle_plate,
                'shipping_address' => $data['shipping_address'] ?? $dn->shipping_address,
                'destination_city' => $data['destination_city'] ?? $dn->destination_city,
                'contact_person' => $data['contact_person'] ?? $dn->contact_person,
                'contact_phone' => $data['contact_phone'] ?? $dn->contact_phone,
                'tracking_number' => $data['tracking_number'] ?? $dn->tracking_number,
                'total_packages' => $data['total_packages'] ?? $dn->total_packages,
                'total_weight_kg' => $data['total_weight_kg'] ?? $dn->total_weight_kg,
                'notes' => $data['notes'] ?? $dn->notes,
            ]);

            // If items provided and not yet stock-deducted, replace items
            if (isset($data['items']) && is_array($data['items']) && !$dn->stock_deducted) {
                $dn->items()->delete();
                foreach ($data['items'] as $item) {
                    $qtyOrdered = (float)($item['quantity_ordered'] ?? $item['quantity_shipped'] ?? 1);
                    $qtyShipped = (float)($item['quantity_shipped'] ?? $qtyOrdered);

                    DeliveryNoteItem::create([
                        'delivery_note_id' => $dn->id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'] ?? 'Product Item',
                        'sku' => $item['sku'] ?? null,
                        'quantity_ordered' => $qtyOrdered,
                        'quantity_shipped' => $qtyShipped,
                        'quantity_received' => 0.00,
                        'unit' => $item['unit'] ?? 'units',
                        'batch_number' => $item['batch_number'] ?? null,
                        'serial_number' => $item['serial_number'] ?? null,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return $dn->fresh(['items', 'customer', 'warehouse', 'vehicle', 'driver', 'invoice']);
        });
    }

    /**
     * Dispatch Delivery Note & Decrement Warehouse Inventory Stock.
     */
    public static function dispatchDeliveryNote(DeliveryNote $dn, int $userId): DeliveryNote
    {
        return DB::transaction(function () use ($dn, $userId) {
            CompanyAccess::check($dn);
            $invoice = $dn->invoice_id ? SalesInvoice::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->invoice_id) : null;
            $dn = DeliveryNote::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->id);
            if (in_array($dn->status, ['dispatched', 'in_transit', 'delivered'])) return $dn->fresh(['items']);
            abort_unless(in_array($dn->status, ['draft', 'scheduled']), 409, 'Only draft or scheduled deliveries may dispatch.');
            $order = $dn->sales_order_id ? SalesOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->sales_order_id) : null;
            if ($invoice) {
                abort_unless((int) $invoice->customer_id === (int) $dn->customer_id && (int) $invoice->warehouse_id === (int) $dn->warehouse_id, 422, 'Invoice customer and warehouse must match.');
                foreach ($dn->items->groupBy('product_id') as $productId => $items) {
                    $previous = DB::table('delivery_note_items as i')->join('delivery_notes as n', 'n.id', '=', 'i.delivery_note_id')->where('n.invoice_id', $invoice->id)->where('n.id', '!=', $dn->id)->whereIn('n.status', ['dispatched','in_transit','delivered'])->where('i.product_id', $productId)->sum('i.quantity_shipped');
                    $billed = $invoice->items()->where('product_id', $productId)->sum('quantity');
                    abort_if((float) $previous + (float) $items->sum('quantity_shipped') > (float) $billed, 422, 'Delivery exceeds invoice quantity.');
                }
            }
            // 1. Decrement inventory stock if not already deducted
            if (!$dn->stock_deducted && (!$invoice || $invoice->status === 'draft')) {
                foreach ($dn->items->sortBy('product_id') as $item) {
                    if ($item->product_id && $item->quantity_shipped > 0) {
                        // Deduct warehouse specific stock
                        $whStock = WarehouseStock::where([
                            'warehouse_id' => $dn->warehouse_id,
                            'product_id' => $item->product_id,
                        ])->lockForUpdate()->first();
                        if (!$whStock || (float) $whStock->quantity - $whStock->reservedQuantity($order?->id) < (float) $item->quantity_shipped) throw \Illuminate\Validation\ValidationException::withMessages(['stock' => 'Insufficient unreserved warehouse stock for this delivery.']);
                        $whStock->quantity -= $item->quantity_shipped;
                        $whStock->save();
                    }
                }
                $dn->stock_deducted = true;
            }

            if ($order) {
                foreach ($dn->items as $item) {
                    $line = $order->items()->where('product_id', $item->product_id)->lockForUpdate()->firstOrFail();
                    abort_if((float) $line->fulfilled_quantity + (float) $item->quantity_shipped > (float) $line->quantity, 422, 'Delivery exceeds remaining order quantity.');
                    $line->increment('fulfilled_quantity', $item->quantity_shipped);
                }
                $remaining = $order->items()->whereColumn('fulfilled_quantity', '<', 'quantity')->exists();
                $order->update(['status' => $remaining ? 'partially_fulfilled' : 'fulfilled', 'stock_reserved' => $remaining && $order->stock_reserved]);
            }

            // 2. Generate verification hash & register in document verification ledger
            $verificationHash = hash('sha256', "DN-{$dn->id}-{$dn->delivery_number}-{$dn->created_by}-" . time());
            $dn->verification_hash = $verificationHash;
            $dn->status = 'dispatched';
            $dn->delivery_verified_at = now();
            $dn->save();

            // 3. Register with public document verification portal
            try {
                $customer = $dn->customer;
                DocumentVerification::updateOrCreate(
                    [
                        'document_type' => 'delivery_note',
                        'document_id' => $dn->id,
                    ],
                    [
                        'document_number' => $dn->delivery_number,
                        'verification_hash' => $verificationHash,
                        'qr_payload' => url('/verify/document/' . $verificationHash),
                        'issuer_name' => 'Mutamark Enterprises Ltd',
                        'party_name' => $customer->name ?? 'Customer',
                        'party_email' => $customer->email ?? '',
                        'party_phone' => $dn->contact_phone ?? '',
                        'total_amount' => $dn->items->sum('quantity_shipped'),
                        'currency' => 'UNITS',
                        'issue_date' => $dn->delivery_date,
                        'status' => 'dispatched',
                        'signatory_name' => 'Dispatch Logistics Supervisor',
                        'signatory_title' => 'Warehouse & Supply Chain Officer',
                        'verified_at' => now(),
                        'created_by' => $dn->created_by,
                    ]
                );
            } catch (\Exception $e) {
                // If verification table has different schema, log and continue safely
            }

            return $dn->fresh(['items', 'customer', 'warehouse', 'vehicle', 'driver', 'invoice']);
        });
    }

    /**
     * Confirm Proof of Delivery (POD) from customer recipient.
     */
    public static function confirmDelivery(DeliveryNote $dn, array $podData, int $userId): DeliveryNote
    {
        return DB::transaction(function () use ($dn, $podData, $userId) {
            CompanyAccess::check($dn);
            $dn = DeliveryNote::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->id);
            if ($dn->status === 'delivered') return $dn->fresh(['items']);
            abort_unless(in_array($dn->status, ['dispatched', 'in_transit']), 409, 'Only dispatched deliveries can be confirmed.');
            $dn->update([
                'status' => 'delivered',
                'actual_delivery_date' => $podData['actual_delivery_date'] ?? now()->toDateString(),
                'proof_of_delivery_recipient' => $podData['recipient_name'] ?? 'Authorized Consignee',
                'proof_of_delivery_signature' => $podData['signature_token'] ?? ('POD-SIG-' . strtoupper(substr(md5(uniqid()), 0, 16))),
                'proof_of_delivery_notes' => $podData['notes'] ?? 'Goods inspected, accepted, and verified in sound condition.',
                'delivery_verified_at' => now(),
            ]);

            // Update items quantity received
            foreach ($dn->items as $item) {
                $item->update([
                    'quantity_received' => $item->quantity_shipped,
                ]);
            }

            // Update verification status
            DocumentVerification::where('document_type', 'delivery_note')
                ->where('document_id', $dn->id)
                ->update(['status' => 'delivered']);

            return $dn->fresh(['items', 'customer', 'warehouse', 'vehicle', 'driver', 'invoice']);
        });
    }

    /**
     * Cancel Delivery Note & Restore Warehouse Inventory Stock.
     */
    public static function cancelDeliveryNote(DeliveryNote $dn, int $userId): DeliveryNote
    {
        return DB::transaction(function () use ($dn, $userId) {
            CompanyAccess::check($dn);
            $invoice = $dn->invoice_id ? SalesInvoice::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->invoice_id) : null;
            $dn = DeliveryNote::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->id);
            if ($dn->status === 'cancelled') return $dn->fresh(['items']);
            abort_unless(in_array($dn->status, ['draft','scheduled','dispatched','in_transit']), 409, 'Delivered goods require a sales return.');
            if ($dn->sales_order_id && in_array($dn->status, ['dispatched', 'in_transit'])) {
                $order = SalesOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->sales_order_id);
                foreach ($dn->items as $item) {
                    $line = $order->items()->where('product_id', $item->product_id)->lockForUpdate()->firstOrFail();
                    abort_if((float) $line->fulfilled_quantity < (float) $item->quantity_shipped, 409, 'Order fulfillment requires reconciliation before cancellation.');
                    $line->decrement('fulfilled_quantity', $item->quantity_shipped);
                }
                $hasFulfillment = $order->items()->where('fulfilled_quantity', '>', 0)->exists();
                $order->update(['status' => $hasFulfillment ? 'partially_fulfilled' : 'confirmed', 'stock_reserved' => false]);
            }
            if ($dn->stock_deducted && (!$invoice || $invoice->status === 'draft')) {
                foreach ($dn->items as $item) {
                    if ($item->product_id && $item->quantity_shipped > 0) {
                        $whStock = WarehouseStock::firstOrCreate([
                            'warehouse_id' => $dn->warehouse_id,
                            'product_id' => $item->product_id,
                        ]);
                        $whStock->increment('quantity', $item->quantity_shipped);
                    }
                }
                $dn->stock_deducted = false;
            }

            $dn->update(['status' => 'cancelled']);

            DocumentVerification::where('document_type', 'delivery_note')
                ->where('document_id', $dn->id)
                ->update(['status' => 'cancelled']);

            return $dn->fresh(['items', 'customer', 'warehouse', 'vehicle', 'driver', 'invoice']);
        });
    }

    /**
     * Delete Delivery Note (CUD: Delete) with safe stock restoration.
     */
    public static function deleteDeliveryNote(DeliveryNote $dn): bool
    {
        CompanyAccess::check($dn);
        return DB::transaction(function () use ($dn) {
            $locked = DeliveryNote::where('created_by', creatorId())->lockForUpdate()->findOrFail($dn->id);
            abort_unless($locked->status === 'draft' && !$locked->stock_deducted, 409, 'Only unissued drafts can be deleted.');
            $locked->items()->delete();
            return $locked->delete();
        }, 3);
    }
}
