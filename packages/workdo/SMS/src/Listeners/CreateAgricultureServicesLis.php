<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureServices;
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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Service')) && company_setting('SMS New Agriculture Service')  == true) {

            if(!empty($agricultureservice))
            {
                $uArr = [
                    'service_name' => $agricultureservice->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Service');
            }
        }
    }
}
