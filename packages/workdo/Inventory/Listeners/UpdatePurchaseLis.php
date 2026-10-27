<?php

namespace Modules\Inventory\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;
use App\Models\Purchase;
use App\Events\UpdatePurchase;

class UpdatePurchaseLis
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
    public function handle(UpdatePurchase $event)
    {
        if (module_is_active('Inventory')) {

            $request  = $event->request;
            $purchase = $event->purchase;

            $products = $purchase->items;
            for ($i = 0; $i < count($products); $i++) {
                $inventory = new Inventory();
                $inventory['product_id']  = $products[$i]['item'];
                $inventory['type']        = 'Purchase';
                $inventory['quantity']    = $products[$i]['quantity'];
                $inventory['description'] = $products[$i]['quantity'] . ' ' . __('Quantity Update by') . ' ' . Purchase::purchaseNumberFormat($request->purchase_id) . '.';
                $inventory['feild_id']    = $request->id;
                $inventory['workspace']   = $request->workspace;
                $inventory['created_by']  = $request->created_by;
                $inventory->save();
            }
        }
    }
}
