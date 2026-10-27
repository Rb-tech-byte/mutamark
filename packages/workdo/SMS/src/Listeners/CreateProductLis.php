<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\ProductService\Events\CreateProduct;
use Workdo\SMS\Entities\SendMsg;



class CreateProductLis
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
    public function handle(CreateProduct $event)
    {
        $user = $event->productService;

        if (module_is_active('SMS')  && !empty(company_setting('SMS New ProductService')) && company_setting('SMS New ProductService') == true) {
            $request = $event->productService;
            $user = \Auth::user();
            if(!empty($user->mobile_no)){
                $uArr = [];
                SendMsg::SendMsgs($user->mobile_no, $uArr , 'New ProductService');
            }
        }
        //

    }
}
