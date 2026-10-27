<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SalesAgent\Events\SalesAgentOrderCreate;
use App\Models\User;

class SalesAgentOrderCreateLis
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
    public function handle(SalesAgentOrderCreate $event)
    {
        $order = $event->order;
        $user = User::find($order->user_id);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Sales Agent Order')) && company_setting('Whatsappapi New Sales Agent Order')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'order_number' => \Modules\SalesAgent\Entities\SalesAgent::purchaseOrderNumberFormat($order->purchaseOrder_id),
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Sales Agent Order');
            }


        }
    }
}
