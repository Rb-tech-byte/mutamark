<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgriculturefleet;
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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Fleet')) && company_setting('SMS New Agriculture Fleet')  == true) {

            if(!empty($agriculture_fleet))
            {
                $uArr = [
                    'fleet_name' => $agriculture_fleet->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Fleet');
            }
        }
    }
}
