<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Account\Events\CreatePayment;


class CreatePaymentLis
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
    public function handle(CreatePayment $event)
    {
       if(module_is_active('SMS') && !empty(company_setting('SMS New Payment')) && company_setting('SMS New Payment')  == true)
        {
            $payment = $event->payment;
            $request = $event->request;
            $vender = \Workdo\Account\Entities\Vender::find($request->vendor_id);
            if(!empty($vender->contact)){
                $uArr = [
                    'amount'=> currency_format_with_sym($request->amount),
                    'vender_name' => $vender->name,
                ];
                SendMsg::SendMsgs($vender->contact, $uArr , 'New Payment');
            }
        }
    }
}
