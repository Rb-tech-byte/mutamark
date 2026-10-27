<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureProcess;
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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Process')) && company_setting('SMS New Agriculture Process')  == true) {

            if(!empty($agricultureprocess))
            {
                $uArr = [
                    'process_name' => $agricultureprocess->name,
                    'hours'  => $agricultureprocess->hours,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Process');
            }
        }
    }
}
