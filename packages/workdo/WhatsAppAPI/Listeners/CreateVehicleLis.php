<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Fleet\Events\CreateVehicle;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Vehicle')) && company_setting('Whatsappapi New Vehicle')  == true) {
            $request = $event->request;
            $vehicale = $event->Vehicle;

            $driver = \Modules\Fleet\Entities\Driver::where('user_id', '=', $request->driver_name)->first();
            if (!empty($driver->phone)) {
                $uArr = [
                    'name' => $vehicale->name,
                ];
                SendMsg::SendMsgs($driver->phone, $uArr , 'New Vehicle');
            }
        }
    }
}
