<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SalesAgent\Events\SalesAgentOrderStatusUpdated;
use App\Models\User;
use Workdo\SalesAgent\Entities\Customer;

class SalesAgentOrderStatusUpdatedLis
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
    public function handle(SalesAgentOrderStatusUpdated $event)
    {
        $order = $event->order;

        if (module_is_active('SMS') && !empty(company_setting('SMS Update Order Status')) && company_setting('SMS Update Order Status')  == true) {
            if(module_is_active('Account')){
                $user = Customer::where('user_id' , $order->user_id)->first();
                if(!empty($user->contact))
                {
                    $uArr = [];
                    SendMsg::SendMsgs($user->contact , $uArr , 'Update Order Status');
                }

            }else{
                $user = User::find($order->user_id);
                if(!empty($user->mobile_no))
                {
                    $uArr = [];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'Update Order Status');
                }

            }



        }
    }
}
