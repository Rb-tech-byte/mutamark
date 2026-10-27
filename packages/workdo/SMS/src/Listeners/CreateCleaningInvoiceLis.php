<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CleaningManagement\Events\CreateCleaningInvoice;
use Workdo\CleaningManagement\Entities\CleaningInspection;

class CreateCleaningInvoiceLis
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
    public function handle(CreateCleaningInvoice $event)
    {
        $invoice = $event->invoice;
        $inspection = CleaningInspection::find($invoice->inspection_id);
        $client = \App\Models\User::where('id',$inspection->cleaning_booking->user_id)->select('name' , 'mobile_no')->first();
        $user = isset($inspection->cleaning_booking->customer_name) ? $inspection->cleaning_booking->customer_name : $client['name'] ?? '' ;

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Cleaning Invoice')) && company_setting('SMS New Cleaning Invoice')  == true) {

            if(!empty($client->mobile_no))
            {
                $uArr = [
                    'user_name' => $user
                ];
                SendMsg::SendMsgs($client->mobile_no , $uArr , 'New Cleaning Invoice');
            }
        }
    }
}
