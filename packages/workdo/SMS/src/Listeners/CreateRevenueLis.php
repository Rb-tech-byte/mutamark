<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Account\Events\CreateRevenue;


class CreateRevenueLis
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
    public function handle(CreateRevenue $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Revenue')) && company_setting('SMS New Revenue')  == true)
        {
            $request = $event->request;
            $customer = \Workdo\Account\Entities\Customer::find($request->customer_id);

            if(!empty($customer->contact)){
                $uArr = [
                    'amount' => currency_format_with_sym($request->amount),
                    'user_name' => $customer['name'],
                ];
                SendMsg::SendMsgs($customer->contact, $uArr , 'New Revenue');
            }
        }
    }
}
