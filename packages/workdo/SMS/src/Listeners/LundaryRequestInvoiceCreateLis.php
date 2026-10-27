<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\LaundryManagement\Events\LundaryRequestInvoiceCreate;
use Workdo\LaundryManagement\Entities\LaundryRequest;
class LundaryRequestInvoiceCreateLis
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
    public function handle(LundaryRequestInvoiceCreate $event)
    {
        $invoic = $event->invoic;
        $laundryrequest = LaundryRequest::find($invoic->laundry_id);

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS Request convert to invoice')) && company_setting('SMS Request convert to invoice')  == true) {

            if(!empty($laundryrequest->phone))
            {
                $uArr = [
                    'user_name' => $laundryrequest->name
                ];
                SendMsg::SendMsgs($laundryrequest->phone, $uArr , 'Request convert to invoice');
            }


        }
    }
}
