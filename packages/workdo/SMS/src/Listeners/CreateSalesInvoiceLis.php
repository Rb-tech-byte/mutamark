<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Illuminate\Support\Facades\Auth;
use Workdo\Sales\Events\CreateSalesInvoice;
use App\Models\User;


class CreateSalesInvoiceLis
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
    public function handle(CreateSalesInvoice $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Sales Invoice')) && company_setting('SMS New Sales Invoice')  == true)
        {
            $invoice = $event->invoice;
            $Assign_user_phone = User::where('id',$invoice->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'sales_invoice_id' => $invoice->invoiceNumberFormat(\Workdo\Sales\Http\Controllers\SalesInvoiceController::invoiceNumber())
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Sales Invoice');
            }
        }
    }
}
