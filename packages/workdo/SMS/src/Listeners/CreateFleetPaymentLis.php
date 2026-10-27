<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Booking;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Fleet\Events\CreateFleetPayment;
use App\Models\User;


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
        if (module_is_active('SMS') && !empty(company_setting('SMS New Booking Payment')) && company_setting('SMS New Booking Payment')  == true)
        {
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
