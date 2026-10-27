<?php

namespace Workdo\ProductService\Listeners;

use Workdo\Pos\Events\CreatePos;
use Workdo\ProductService\Models\WarehouseStock;

class PosCreateListener
{
    public function handle(CreatePos $event)
    {
        $posSale = $event->posSale;
        foreach ($posSale->items()->get()->sortBy('product_id') as $item) {
            $stock = WarehouseStock::where('warehouse_id', $posSale->warehouse_id)
                ->where('product_id', $item->product_id)
                ->lockForUpdate()->first();
            if (!$stock || (float) $stock->quantity - $stock->reservedQuantity() < (float) $item->quantity) throw \Illuminate\Validation\ValidationException::withMessages(['stock'=>'Insufficient unreserved stock for this POS sale.']);
            $stock->decrement('quantity', $item->quantity);
        }
    }
}
