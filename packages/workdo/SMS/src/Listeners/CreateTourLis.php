<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\TourTravelManagement\Events\CreateTour;
use Workdo\SMS\Entities\SendMsg;
class CreateTourLis
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
    public function handle(CreateTour $event)
    {
        $tour = $event->tour;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Tour')) && company_setting('SMS New Tour')  == true) {
            if(!empty($tour) && !empty($to))
            {
                $uArr = [
                    'tour_name' => $tour->tour_name,
                    'days' => $tour->tour_days
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Tour');
            }
        }
    }
}
