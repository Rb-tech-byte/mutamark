<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\Fleet\Entities\Booking;
use Modules\WhatsAppAPI\Entities\SendMsg;

class CreateBookingLis
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
    public function handle($event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Booking')) && company_setting('Whatsappapi New Booking')  == true) {
            $request = $event->bookings;

            $book = Booking::find($request->id);
            $driver = \Modules\Fleet\Entities\Driver::where('id', '=', $request->driver_name)->first();

            if (!empty($driver->phone)) {
                $uArr = [
                    'user_name' => $book->BookingUser->name
                ];
                SendMsg::SendMsgs($driver->phone, $uArr , 'New Booking');
            }
        }
    }
}
