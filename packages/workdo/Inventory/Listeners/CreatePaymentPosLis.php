<?php

namespace Modules\Inventory\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;
use Modules\Pos\Entities\Pos;
use Modules\Pos\Events\CreatePaymentPos;

class CreatePaymentPosLis
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
    public function handle(CreatePaymentPos $event)
    {

        if (module_is_active('Inventory')) {

            $pos     = $event->pos;
            $request = $event->request;
            $sales            = session()->get('pos');
            if (isset($sales) && !empty($sales) && count($sales) > 0) {
                foreach ($sales as $key => $value) {
                    $product_id = $value['id'];

                    $inventory = new Inventory();
                    $inventory['product_id']  = $product_id;
                    $inventory['type']        = 'POS Invoice';
                    $inventory['quantity']    = $value['quantity'];
                    $inventory['description'] = $value['quantity'] . ' ' . __('Quantity decrease by') . ' ' . Pos::posNumberFormat($request->pos_id) . '.';
                    $inventory['feild_id']    = $request->pos_id;
                    $inventory['workspace']   = $request->workspace;
                    $inventory['created_by']  = $request->created_by;
                    $inventory->save();
                }
            }
        }
    }
}
