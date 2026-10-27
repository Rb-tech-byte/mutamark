<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CMMS\Events\CreateLocation;

class CreateLocationLis
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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Location')) && company_setting('Whatsappapi New Location')  == true)
        {
            $request = $event->request;
            $location = $request->name;
            $company = User::find($event->location->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($location) && !empty($to)){
                $uArr = [
                    'location_name' => $location,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Location');
            }
        }
    }
}
