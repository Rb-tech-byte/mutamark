<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ConsignmentManagement\Events\CreateConsignment;
class CreateConsignmentLis
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
    public function handle(CreateConsignment $event)
    {
        $consignment = $event->consignment;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Consignment')) && company_setting('SMS New Consignment')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'consignment_name' => $consignment->title,
                    'commission' => $consignment->commission
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Consignment');
            }
        }
    }
}
