<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Account\Events\CreateBill;

class CreateBillLis
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
    public function handle(CreateBill $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Bill')) && company_setting('SMS New Bill')  == true)
        {
            $request = $event->request;
            $bill = $event->bill;
            $vendor = \Workdo\Account\Entities\Vender::find($request->vendor_id);
            if(!empty($vendor->contact)){

                $uArr = [
                    'bill_id'=>\Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id),
                ];
                SendMsg::SendMsgs($vendor->contact, $uArr , 'New Bill');
            }
        }    }
}
