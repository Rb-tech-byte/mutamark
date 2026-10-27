<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\WasteManagement\Events\WasteCollectionRequestCreate;
use Workdo\WasteManagement\Entities\WasteLocation;

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

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Collection Request')) && company_setting('SMS New Collection Request')  == true) {

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
