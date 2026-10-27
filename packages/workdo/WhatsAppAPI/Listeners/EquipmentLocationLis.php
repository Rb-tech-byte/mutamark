<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateLocation;
use App\Models\User;

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

        $user = User::find($location->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Location')) && company_setting('Whatsappapi New Location')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'location_name' => $location->location_name
                ];
                
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Location');
            }

        }
    }
}
