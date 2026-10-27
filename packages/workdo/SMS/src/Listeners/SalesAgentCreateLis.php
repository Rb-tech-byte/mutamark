<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SalesAgent\Events\SalesAgentCreate;
use App\Models\User;
use Workdo\SalesAgent\Entities\Customer;


class SalesAgentCreateLis
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
    public function handle(SalesAgentCreate $event)
    {
        $salesagent = $event->salesagent;

        $user = User::find($salesagent->created_by);
        $customer = Customer::where('customer_id', $salesagent->customer_id)->first();

        if (module_is_active('SMS') && !empty(company_setting('SMS New Sales Agent')) && company_setting('SMS New Sales Agent')  == true) {

            if(!empty($user) && !empty($customer->mobile_no))
            {
                $uArr = [
                    'name' => $customer->name,
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($customer->mobile_no , $uArr , 'New Sales Agent');
            }


        }
    }
}
