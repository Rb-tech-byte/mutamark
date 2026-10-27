<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SalesAgent\Events\SalesAgentOrderStatusUpdated;
use App\Models\User;

class SalesAgentOrderStatusUpdatedLis
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
    public function handle(SalesAgentOrderStatusUpdated $event)
    {
        $order = $event->order;

        $user = User::find($order->user_id);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Update Order Status')) && company_setting('Whatsappapi Update Order Status')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'Update Order Status');
            }


        }
    }
}
