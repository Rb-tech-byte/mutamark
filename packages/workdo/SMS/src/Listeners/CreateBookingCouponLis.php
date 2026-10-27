<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Holidayz\Events\CreateBookingCoupon;
use App\Models\User;

class CreateBookingCouponLis
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
    public function handle(CreateBookingCoupon $event)
    {
        $coupon = ($event->coupon);

        $user = User::find($coupon->created_by);

        if(module_is_active('SMS') && !empty(company_setting('SMS New Booking Coupon')) && company_setting('SMS New Booking Coupon')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'coupon_name' => $coupon->name,
                    'user_name' => $user->name,
                    'discount'  => $coupon->discount,
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Booking Coupon');
            }
        }
    }
}
