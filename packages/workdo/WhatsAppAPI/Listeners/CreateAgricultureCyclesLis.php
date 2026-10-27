<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureCycles;
use Illuminate\Support\Facades\Auth;

class CreateAgricultureCyclesLis
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
    public function handle(CreateAgricultureCycles $event)
    {
        $agriculturecycles = $event->agriculturecycles;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture cycle')) && company_setting('Whatsappapi New Agriculture cycle')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agriculturecycles) && !empty($to))
            {
                $uArr = [
                    'cycle_name' => $agriculturecycles->name,
                ];

                SendMsg::SendMsgs($to,$uArr , 'New Agriculture cycle');
            }
        }
    }
}
