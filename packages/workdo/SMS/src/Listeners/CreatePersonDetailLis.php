<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\TourTravelManagement\Events\CreatePersonDetail;
use Workdo\TourTravelManagement\Entities\Tour;

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
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Person Detail')) && company_setting('SMS New Person Detail')  == true) {

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
