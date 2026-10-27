<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SalesAgent\Events\SalesAgentOrderCreate;
use App\Models\User;
use Workdo\SalesAgent\Entities\Customer;




class SalesAgentOrderCreateLis
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
    public function handle(SalesAgentOrderCreate $event)
    {
        $order = $event->order;
        if(module_is_active('Account')){
            $user = Customer::where('customer_id', $order->user_id)->first();
            if (module_is_active('SMS') && !empty(company_setting('SMS New Sales Agent Order')) && company_setting('SMS New Sales Agent Order')  == true) {

                if(!empty($user->contact))
                {
                    $uArr = [
                        'order_number' => \Workdo\SalesAgent\Entities\SalesAgent::purchaseOrderNumberFormat($order->purchaseOrder_id),
                        'user_name' => $user->name
                    ];
                    SendMsg::SendMsgs($user->contact , $uArr , 'New Sales Agent Order');
                }


            }
        }else{
            $user = User::find($order->user_id);
            if(!empty($user->contact))
            {
                $uArr = [
                    'order_number' => \Workdo\SalesAgent\Entities\SalesAgent::purchaseOrderNumberFormat($order->purchaseOrder_id),
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($user->contact , $uArr , 'New Sales Agent Order');
            }

        }



    }
}
