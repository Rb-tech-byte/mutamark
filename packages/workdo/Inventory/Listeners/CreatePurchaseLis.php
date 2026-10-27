<?php

namespace Modules\Inventory\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;
use App\Models\Purchase;
use App\Events\CreatePurchase;

class CreatePurchaseLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CreatePurchase $event)
    {

        if (module_is_active('Inventory')) {

            $request  = $event->request;
            $purchase = $event->purchase;

            $products = $request->items;
            for ($i = 0; $i < count($products); $i++) {
                $inventory = new Inventory();
                $inventory['product_id']  = $products[$i]['item'];
                $inventory['type']        = 'Purchase';
                $inventory['quantity']    = $products[$i]['quantity'];
                $inventory['description'] = $products[$i]['quantity'] . ' ' . __('Quantity Increase by') . ' ' . Purchase::purchaseNumberFormat($purchase->purchase_id) . '.';
                $inventory['feild_id']    = $purchase->id;
                $inventory['workspace']   = $purchase->workspace;
                $inventory['created_by']  = $purchase->created_by;
                $inventory->save();
            }
        }
    }
}
