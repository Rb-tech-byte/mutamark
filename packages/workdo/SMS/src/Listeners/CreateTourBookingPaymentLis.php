<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\TourTravelManagement\Events\CreateTourBookingPayment;
use Workdo\TourTravelManagement\Entities\Tour;
use Workdo\TourTravelManagement\Entities\TourInquiry;

class CreateTourBookingPaymentLis
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
    public function handle(CreateTourBookingPayment $event)
    {
        $payment = $event->payment;
        $tour = Tour::find($payment->tour_id);
        $inquiry = TourInquiry::find($payment->inquiry_id);
        $mobileNo = $inquiry->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Tour Booking Payment')) && company_setting('SMS New Tour Booking Payment')  == true) {
            if(!empty($tour) && !empty($inquiry) && !empty($payment) && !empty($mobileNo))
            {
                $uArr = [
                    'tour_name' => $tour->tour_name,
                    'user_name' => $inquiry->person_name,
                    'amount' => $payment->amount,
                ];
                SendMsg::SendMsgs($mobileNo , $uArr , 'New Tour Booking Payment');
            }
        }
    }
}
