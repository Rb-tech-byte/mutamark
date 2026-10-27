<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ConsignmentManagement\Events\CreateProduct;

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
        $product = $event->product;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Consignment Product')) && company_setting('Whatsappapi New Consignment Product')  == true) {

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
