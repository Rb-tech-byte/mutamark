<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\CleaningManagement\Events\CreateCleaningBooking;
use Workdo\SMS\Entities\SendMsg;
use App\Models\User;
class CreateCleaningBookingLis
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
    public function handle(CreateCleaningBooking $event)
    {
        $booking = $event->booking;
        $user = User::find($booking->user_id);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Cleaning Booking')) && company_setting('SMS New Cleaning Booking')  == true) {

            if(!empty($booking) && !empty($user) && !empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $booking->customer_name != null ? $booking->customer_name : $user->name ?? '',
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Cleaning Booking');
            }
        }
    }
}
