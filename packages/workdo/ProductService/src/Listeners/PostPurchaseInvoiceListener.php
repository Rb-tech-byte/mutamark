<?php

namespace Workdo\ProductService\Listeners;

use App\Events\PostPurchaseInvoice;
use Workdo\ProductService\Models\WarehouseStock;

class PostPurchaseInvoiceListener
{
    public function handle(PostPurchaseInvoice $event)
    {
        $purchaseInvoice = $event->purchaseInvoice;
        if ($purchaseInvoice->purchase_order_id) return; // PO stock is received once, by its posted GRNs.
        foreach ($purchaseInvoice->items()->get()->groupBy('product_id')->sortKeys() as $productId => $items) {
            $received = \Illuminate\Support\Facades\DB::table('grn_items as items')->join('goods_received_notes as notes', 'notes.id', '=', 'items.grn_id')
                ->where('notes.purchase_invoice_id', $purchaseInvoice->id)->where('notes.status', 'posted')->where('items.product_id', $productId)->sum('items.accepted_quantity');
            $quantity = max(0, (float) $items->sum('quantity') - (float) $received);
            $stock = WarehouseStock::where('warehouse_id', $purchaseInvoice->warehouse_id)
                ->where('product_id', $productId)->lockForUpdate()->first();
            if ($stock) {
                $stock->increment('quantity', $quantity);
            } else {
                WarehouseStock::create([
                    'warehouse_id' => $purchaseInvoice->warehouse_id,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }
        }
    }
}
