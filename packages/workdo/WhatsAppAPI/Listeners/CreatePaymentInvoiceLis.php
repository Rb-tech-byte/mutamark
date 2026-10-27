<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Events\CreatePaymentInvoice;

class CreatePaymentInvoiceLis
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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Invoice Status Updated')) && company_setting('Whatsappapi Invoice Status Updated')  == true)
        {
            $invoice = $event->invoice;
            $customer = \Modules\Account\Entities\Customer::where('user_id',$invoice->user_id)->first();
            if(!empty($customer))
            {
                $customer->mobile_no = $customer->contact;
            }
            if(empty($customer))
            {
                $customer =User::where('id',$invoice->user_id)->first();
            }
            if(!empty($customer->mobile_no)){
                $uArr = [];
                SendMsg::SendMsgs($customer->mobile_no ,$uArr , 'Invoice Status Updated');
            }
        }
    }
}
