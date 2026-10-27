<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateLocation;
use Illuminate\Support\Facades\Auth;

class EquipmentLocationLis
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
    public function handle(CreateLocation $event)
    {
        $location = $event->location;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Location')) && company_setting('SMS New Location')  == true) {

            $uArr = [
                'location_name' => $location->location_name
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to,$uArr , 'New Location');

        }
    }
}
