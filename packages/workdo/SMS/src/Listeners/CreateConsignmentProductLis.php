<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ConsignmentManagement\Events\CreateProduct;
class CreateConsignmentProductLis
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
        $product = $event->product;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Consignment Product')) && company_setting('SMS New Consignment Product')  == true) {

            if(!empty($product) && !empty($to))
            {
                $uArr = [
                    'product_name' => $product->name
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Consignment Product');
            }
        }
    }
}
