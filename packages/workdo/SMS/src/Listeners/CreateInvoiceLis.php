<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use App\Events\CreateInvoice;
use App\Models\Invoice;
use App\Models\User;

class CreateInvoiceLis
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
    public function handle(CreateInvoice $event)
    {

        if(module_is_active('SMS') && !empty(company_setting('SMS New Invoice')) && company_setting('SMS New Invoice')  == true)
        {
            $invoice = $event->invoice;
            $request = $event->request;
            $user = \Workdo\Account\Entities\Customer::where('user_id',$request->customer_id)->first();
            if(!empty($user)){
                $user->mobile_no = $user->contact;
            }
            if(empty($user))
            {
                $user =User::where('id',$request->customer_id)->first();
            }
            if(!empty($user->mobile_no)){
                $uArr = [
                    'invoice_id' => \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id),
                ];
              SendMsg::SendMsgs($user->mobile_no,$uArr ,'New Invoice');
            }
        }
    }
}
