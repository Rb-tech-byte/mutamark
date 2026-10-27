<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgriculturefleet;
use Illuminate\Support\Facades\Auth;

class CreateAgriculturefleetLis
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
    public function handle(CreateAgriculturefleet $event)
    {

        $agriculture_fleet = $event->agriculture_fleet;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Fleet')) && company_setting('Whatsappapi New Agriculture Fleet')  == true) {
            $to = Auth::user()->mobile_no;
            if(!empty($agriculture_fleet) && !empty($to))
            {
                $uArr = [
                    'fleet_name' => $agriculture_fleet->name,
                ];
               
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Fleet');
            }
        }
    }
}
