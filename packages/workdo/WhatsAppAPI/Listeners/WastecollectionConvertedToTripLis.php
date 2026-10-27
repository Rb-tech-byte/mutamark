<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\WasteManagement\Events\WastecollectionConvertedToTrip;
use Modules\WasteManagement\Entities\WasteCollection;

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
        
        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Collection Converted To Trip')) && company_setting('Whatsappapi Collection Converted To Trip')  == true) {

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
