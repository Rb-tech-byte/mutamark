<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
class CreatePaymentRetainerLis
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
        $retainer = $event->retainer;
        $customer = \Workdo\Account\Entities\Customer::where('user_id',$retainer->customer_id)->first();
        $customer->mobile_no = $customer->contact;
        if(!empty($retainer)){

            if(module_is_active('SMS',$retainer->created_by) && !empty(company_setting('SMS New Retainer Payment',$retainer->created_by,$retainer->workspace)) && company_setting('SMS New Retainer Payment',$retainer->created_by,$retainer->workspace)  == true)
            {

            if(!empty($customer->mobile_no)){

                $uArr = [];
                SendMsg::SendMsgs($customer->mobile_no , $uArr,'New Retainer Payment',$retainer->created_by,$retainer->workspace);
            }
            }
        }
    }
}
