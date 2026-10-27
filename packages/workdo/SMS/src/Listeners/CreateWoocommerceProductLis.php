<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\WordpressWoocommerce\Events\CreateWoocommerceProduct;
use Illuminate\Support\Facades\Auth;

class CreateWoocommerceProductLis
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
    public function handle(CreateWoocommerceProduct $event)
    {
        $product = $event->request;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Product')) && company_setting('SMS New Product')  == true) {

            $uArr = [
                'name' => $product['name']
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to,$uArr , 'New Product');

        }
    }
}
