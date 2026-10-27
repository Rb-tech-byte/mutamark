<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Illuminate\WhatsAppAPI\Facades\Auth;
use Modules\Sales\Events\CreateSalesInvoice;

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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Sales Invoice')) && company_setting('Whatsappapi New Sales Invoice')  == true)
        {
            $invoice = $event->invoice;
            $Assign_user_phone = User::where('id',$invoice->user_id)->first();

            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'sales_invoice_id' => $invoice->invoiceNumberFormat(\Modules\Sales\Http\Controllers\SalesInvoiceController::invoiceNumber())
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Sales Invoice');
            }
        }
    }
}
