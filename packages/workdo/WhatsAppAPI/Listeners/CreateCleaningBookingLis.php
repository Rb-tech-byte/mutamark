<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\CleaningManagement\Events\CreateCleaningBooking;
use Modules\WhatsAppAPI\Entities\SendMsg;
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

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Cleaning Booking')) && company_setting('Whatsappapi New Cleaning Booking')  == true) {

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
