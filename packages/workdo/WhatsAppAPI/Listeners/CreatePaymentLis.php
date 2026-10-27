<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Account\Events\CreatePayment;

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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Payment')) && company_setting('Whatsappapi New Payment')  == true)
        {
            $payment = $event->payment;
            $request = $event->request;
            $vender = \Modules\Account\Entities\Vender::find($request->vendor_id);
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
