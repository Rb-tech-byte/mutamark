<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Account\Events\CreateVendor;

class CreateVendorLis
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
    public function handle(CreateVendor $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Vendor')) && company_setting('Whatsappapi New Vendor')  == true)
        {
            $request = $event->request;
            if(!empty($request->contact)){
                $uArr = [];

                SendMsg::SendMsgs($request->contact, $uArr , 'New Vendor');
            }
        }
    }
}
