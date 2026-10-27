<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\WasteManagement\Events\WastecollectionConvertedToTrip;
use Workdo\WasteManagement\Entities\WasteCollection;
class WastecollectionConvertedToTripLis
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
    public function handle(WastecollectionConvertedToTrip $event)
    {
        $WasteCollection = $event->WasteCollection;
        $collectionReq = WasteCollection::where('request_id',$WasteCollection->request_id)->first();

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS Collection Converted To Trip')) && company_setting('SMS Collection Converted To Trip')  == true) {

            if(!empty($collectionReq) && !empty($WasteCollection) && !empty($WasteCollection->phone))
            {
                $uArr = [
                    'user_name' => $collectionReq->name
                ];
                SendMsg::SendMsgs($WasteCollection->phone, $uArr , 'Collection Converted To Trip');
            }


        }
    }
}
