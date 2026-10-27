<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\TourTravelManagement\Events\CreateTourBooking;
use Modules\TourTravelManagement\Entities\Tour;
use Modules\TourTravelManagement\Entities\TourInquiry;

class CreateTourBookingLis
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
    public function handle(CreateTourBooking $event)
    {
        $tour_booking = $event->tour_booking;
        $tour = Tour::find($tour_booking->tour_id);
        $inquiry = TourInquiry::find($tour_booking->inquiry_id);
        $mobileNo = $inquiry->mobile_no;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Tour Booking')) && company_setting('Whatsappapi New Tour Booking')  == true) {

            if(!empty($tour) && !empty($inquiry) && !empty($mobileNo))
            {
                $uArr = [
                    'tour_name' => $tour->tour_name,
                    'user_name' => $inquiry->person_name,
                ];
                SendMsg::SendMsgs($mobileNo , $uArr , 'New Tour Booking');
            }


        }
    }
}
