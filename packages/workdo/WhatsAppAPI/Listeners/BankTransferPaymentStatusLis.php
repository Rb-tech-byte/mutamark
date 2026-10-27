<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Events\BankTransferPaymentStatus;
use App\Models\Invoice;
use App\Models\User;

class BankTransferPaymentStatusLis
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
    public function handle(BankTransferPaymentStatus $event)
    {
        $data = $event->data;

        $invoice = Invoice::find($data->invoice_id);

        $user = \Modules\Account\Entities\Customer::where('user_id',$invoice->user_id)->first();
        if(!empty($user)){
            $user->mobile_no = $user->contact;
        }
        if(empty($user))
        {
            $user =User::where('id',$invoice->user_id)->first();
            $user->mobile_no = $user->mobile_no;

        }

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Bank Transfer Payment Status Updated')) && company_setting('Whatsappapi Bank Transfer Payment Status Updated')  == true) {

            if(!empty($data) && !empty($user->mobile_no))
            {
                $uArr = [
                    'invoice_id' => \App\Models\Invoice::invoiceNumberFormat($data->invoice_id)
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'Bank Transfer Payment Status Updated');
            }

        }
    }
}
