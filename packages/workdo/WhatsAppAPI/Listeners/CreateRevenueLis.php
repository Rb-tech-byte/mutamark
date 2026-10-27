<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Account\Events\CreateRevenue;

class CreateRevenueLis
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
    public function handle(CreateRevenue $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Revenue')) && company_setting('Whatsappapi New Revenue')  == true)
        {
            $request = $event->request;
            $customer = \Modules\Account\Entities\Customer::find($request->customer_id);

            if(!empty($customer->contact)){
                $uArr = [
                    'amount' => currency_format_with_sym($request->amount),
                    'user_name' => $customer['name'],
                ];
                SendMsg::SendMsgs($customer->contact, $uArr , 'New Revenue');
            }
        }
    }
}
