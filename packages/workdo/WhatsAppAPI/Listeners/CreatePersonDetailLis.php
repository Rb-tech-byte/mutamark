<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\TourTravelManagement\Events\CreatePersonDetail;
use Modules\TourTravelManagement\Entities\Tour;

class CreatePersonDetailLis
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
    public function handle(CreatePersonDetail $event)
    {
        $person_information = $event->person_information;
        $tour = Tour::find($person_information->tour_id);
        $mobileNo = $person_information->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Person Detail')) && company_setting('Whatsappapi New Person Detail')  == true) {

            if(!empty($mobileNo))
            {
                $uArr = [
                    'tour_name' => $tour->tour_name,
                ];
                SendMsg::SendMsgs($mobileNo, $uArr , 'New Person Detail');
            }


        }
    }
}
