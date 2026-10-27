<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Account\Events\CreateCustomer;



class CreateCustomerLis
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
    public function handle(CreateCustomer $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Customer')) && company_setting('SMS New Customer')  == true)
        {
            $request = $event->request;

            if(!empty($request->contact)){
                $uArr = [];
                SendMsg::SendMsgs($request->contact, $uArr , 'New Customer');
            }
        }
    }
}
