<?php

namespace Workdo\ProductService\Listeners;

use App\Events\PostSalesInvoice;
use Workdo\ProductService\Models\WarehouseStock;

class PostSalesInvoiceListener
{
    public function handle(PostSalesInvoice $event)
    {
        $salesInvoice = $event->salesInvoice;
        
        if ($salesInvoice->type === 'product') {
            foreach ($salesInvoice->items()->get()->groupBy('product_id')->sortKeys() as $productId => $items) {
                $delivered = \Illuminate\Support\Facades\DB::table('delivery_note_items as items')->join('delivery_notes as notes', 'notes.id', '=', 'items.delivery_note_id')
                    ->where('notes.invoice_id', $salesInvoice->id)->where('notes.stock_deducted', true)->whereNotIn('notes.status', ['cancelled', 'draft', 'scheduled'])
                    ->where('items.product_id', $productId)->sum('items.quantity_shipped');
                $quantity = max(0, (float) $items->sum('quantity') - (float) $delivered);
                $stock = WarehouseStock::where('warehouse_id', $salesInvoice->warehouse_id)
                    ->where('product_id', $productId)->lockForUpdate()->first();
                if ($quantity > 0 && (!$stock || (float) $stock->quantity - $stock->reservedQuantity($salesInvoice->sales_order_id) < $quantity)) throw \Illuminate\Validation\ValidationException::withMessages(['stock' => 'Insufficient unreserved warehouse stock to post this invoice.']);
                if ($quantity > 0) $stock->decrement('quantity', $quantity);
            }
        }
    }
}
