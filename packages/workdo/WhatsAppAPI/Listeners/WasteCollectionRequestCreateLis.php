<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\WasteManagement\Events\WasteCollectionRequestCreate;
use Modules\WasteManagement\Entities\WasteLocation;

class WasteCollectionRequestCreateLis
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
    public function handle(WasteCollectionRequestCreate $event)
    {
        $WasteCollection = $event->WasteCollection;
        $location  = WasteLocation::find($WasteCollection->location_id);
        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Collection Request')) && company_setting('Whatsappapi New Collection Request')  == true) {

            if(!empty($WasteCollection) && !empty($location) && !empty($WasteCollection->phone))
            {
                $uArr = [
                    'user_name' => $WasteCollection->name,
                    'location_name' => $location->name
                ];
                SendMsg::SendMsgs($WasteCollection->phone, $uArr , 'New Collection Request');
            }
        }
    }
}
