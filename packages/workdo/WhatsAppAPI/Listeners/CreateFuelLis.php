<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Fleet\Events\CreateFuel;

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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Fuel')) && company_setting('Whatsappapi New Fuel')  == true) {

            $request = $event->request;
            $driver = \Modules\Fleet\Entities\Driver::where('id', '=', $request->driver_name)->first();

            if (!empty($driver->phone)) {
                $uArr = [];
                SendMsg::SendMsgs($driver->phone, $uArr , 'New Fuel');
            }
        }
    }
}
