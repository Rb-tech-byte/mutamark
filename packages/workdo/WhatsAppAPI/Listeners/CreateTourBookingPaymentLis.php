<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\TourTravelManagement\Events\CreateTourBookingPayment;
use Modules\TourTravelManagement\Entities\Tour;
use Modules\TourTravelManagement\Entities\TourInquiry;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Tour Booking Payment')) && company_setting('Whatsappapi New Tour Booking Payment')  == true) {

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
