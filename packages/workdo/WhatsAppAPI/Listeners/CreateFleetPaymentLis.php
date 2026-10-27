<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\Fleet\Entities\Booking;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Fleet\Events\CreateFleetPayment;

class CreateFleetPaymentLis
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
    public function handle(CreateFleetPayment $event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Booking Payment')) && company_setting('Whatsappapi New Booking Payment')  == true) {
            $request = $event->Payment;
            $payment = Booking::find($request->booking_id);
            $customer =  User::where('id', '=', $payment->customer_name)->first();

            if (!empty($customer->mobile_no)) {
                $uArr = [
                    'user_name' => $payment->BookingUser->name
                ];
                SendMsg::SendMsgs($customer->mobile_no, $uArr , 'New Booking Payment');
            }
        }
    }
}
