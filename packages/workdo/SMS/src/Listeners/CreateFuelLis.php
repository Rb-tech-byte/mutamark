<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Fleet\Events\CreateFuel;
class CreateFuelLis
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
    public function handle(CreateFuel $event)
    {
        if (module_is_active('SMS') && !empty(company_setting('SMS New Fuel')) && company_setting('SMS New Fuel')  == true) {

            $request = $event->request;
            $driver = \Workdo\Fleet\Entities\Driver::where('id', '=', $request->driver_name)->first();

            if (!empty($driver->phone)) {
                $uArr = [];
                SendMsg::SendMsgs($driver->phone, $uArr , 'New Fuel');
            }
        }
    }
}
