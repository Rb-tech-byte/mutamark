<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\WordpressWoocommerce\Events\CreateWoocommerceProduct;
use App\Models\User;

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

        $userProduct = $event->Product;
        $user = User::find($userProduct->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Product')) && company_setting('Whatsappapi New Product')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $product['name']
                ];
                SendMsg::SendMsgs($user->mobile_no,  $uArr , 'New Product');
            }


        }
    }
}
