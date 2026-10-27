<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Fleet\Events\CreateVehicle;

class CreateVehicleLis
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
    public function handle(CreateVehicle $event)
    {
        if (module_is_active('SMS') && !empty(company_setting('SMS New Vehicle')) && company_setting('SMS New Vehicle')  == true) {
            $request = $event->request;
            $vehicale = $event->Vehicle;

            $driver = \Workdo\Fleet\Entities\Driver::where('id', '=', $request->driver_name)->first();
            if (!empty($driver->phone)) {

                $uArr = [
                    'name' => $vehicale->name,
                ];
                SendMsg::SendMsgs($driver->phone, $uArr , 'New Vehicle');
            }
        }
    }
}
