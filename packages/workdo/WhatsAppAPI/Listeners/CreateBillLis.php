<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Account\Events\CreateBill;

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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Bill')) && company_setting('Whatsappapi New Bill')  == true)
        {
            $request = $event->request;
            $bill = $event->bill;
            $vendor = \Modules\Account\Entities\Vender::find($request->vendor_id);
            if(!empty($vendor))
            {
                $vendor->contact = $vendor->contact;

            }
            else
            {
                $vendor = User::where('id',$request->vender_id)->first();
            }
            if(!empty($vendor->contact)){
                $uArr = [
                    'bill_id'=>\Modules\Account\Entities\Bill::billNumberFormat($bill->bill_id),
                ];
                SendMsg::SendMsgs($vendor->contact, $uArr , 'New Bill');
            }
        }
    }
}
