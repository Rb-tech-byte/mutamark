<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureServices;
use Illuminate\Support\Facades\Auth;


class CreateAgricultureServicesLis
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
    public function handle(CreateAgricultureServices $event)
    {
        $agricultureservice = $event->agricultureservice;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Service')) && company_setting('Whatsappapi New Agriculture Service')  == true) {
            $to = Auth::user()->mobile_no;

            if(!empty($agricultureservice) && !empty($to))
            {
                $uArr = [
                    'service_name' => $agricultureservice->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Service');
            }
        }
    }
}
