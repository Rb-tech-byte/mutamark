<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Account\Events\CreateCustomer;

class CreateCustomerLis
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
    public function handle(CreateCustomer $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Customer')) && company_setting('Whatsappapi New Customer')  == true)
        {
            $request = $event->request;
            if(!empty($request->contact)){
                $uArr = [];
                SendMsg::SendMsgs($request->contact, $uArr , 'New Customer');
            }
        }
    }
}
