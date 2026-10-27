<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Holidayz\Events\CreateHotelCustomer;
class CreateHotelCustomerLis
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
    public function handle(CreateHotelCustomer $event)
    {

        if(module_is_active('SMS') && !empty(company_setting('SMS New Hotel Customer')) && company_setting('SMS New Hotel Customer')  == true)
        {
            $request = $event->request;
            if(!empty(\Auth::guard('holiday')->user()))
            {
                $uArr = [
                    'user_name' => \Auth::guard('holiday')->user()->name
                ];

                SendMsg::SendMsgs(\Auth::user()->mobile_no , $uArr , 'New Hotel Customer');
            }else{

                $uArr = [
                    'user_name' => \Auth::user()->name
                ];

                SendMsg::SendMsgs($request->mobile_phone, $uArr , 'New Hotel Customer');

            }
        }
    }
}
