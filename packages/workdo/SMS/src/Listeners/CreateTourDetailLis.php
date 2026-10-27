<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\TourTravelManagement\Events\CreateTourDetail;
use Workdo\SMS\Entities\SendMsg;
use Workdo\TourTravelManagement\Entities\Tour;
class CreateTourDetailLis
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
    public function handle(CreateTourDetail $event)
    {
        $tour = $event->tour;
        $tourName = Tour::find($tour->tour_id);
        $to =\Auth::user()->mobile_no;
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('sms New Tour Detail')) && company_setting('sms New Tour Detail')  == true) {

            if(!empty($tour) && !empty($to))
            {
                $uArr = [
                    'tour_name' => $tourName->tour_name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Tour Detail');
            }


        }
    }
}
