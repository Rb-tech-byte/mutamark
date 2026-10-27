<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;

class CreatePaymentRetainerLis
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
    public function handle($event)
    {
        $retainer = $event->retainer;
        $customer = \Modules\Account\Entities\Customer::where('user_id',$retainer->customer_id)->first();
        $customer->mobile_no = $customer->contact;
        if(!empty($retainer)){

            if(module_is_active('WhatsAppAPI',$retainer->created_by) && company_setting('whatsappapi_notification_is' , $retainer->created_by , $retainer->workspace)=='on' 
            && !empty(company_setting('WhatsAppAPI New Retainer Payment',$retainer->created_by,$retainer->workspace)) && company_setting('WhatsAppAPI New Retainer Payment',$retainer->created_by,$retainer->workspace)  == true)
            {
                if(!empty($customer->mobile_no)){
                $uArr = [];
                SendMsg::SendMsgs($customer->mobile_no , $uArr,'New Retainer Payment',$retainer->created_by,$retainer->workspace);
                }
            }
        }
    }
}
