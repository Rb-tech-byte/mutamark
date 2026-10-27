<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureProcess;
use Illuminate\Support\Facades\Auth;

class CreateAgricultureProcessLis
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
    public function handle(CreateAgricultureProcess $event)
    {
        $agricultureprocess = $event->agricultureprocess;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Process')) && company_setting('Whatsappapi New Agriculture Process')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agricultureprocess) && !empty($to))
            {
                $uArr = [
                    'process_name' => $agricultureprocess->name,
                    'hours'  => $agricultureprocess->hours,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Process');
            }
        }
    }
}
