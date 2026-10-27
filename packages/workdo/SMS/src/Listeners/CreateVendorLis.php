<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Events\CreateVendor;
use Workdo\SMS\Entities\SendMsg;


class CreateVendorLis
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
    public function handle(CreateVendor $event)
    {
       if(module_is_active('SMS') && !empty(company_setting('SMS New Vendor')) && company_setting('SMS New Vendor')  == true)
        {
            $request = $event->request;
            if(!empty($request->contact)){
                $uArr = [];

                SendMsg::SendMsgs($request->contact, $uArr , 'New Vendor');
            }
        }
    }
}
