<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ConsignmentManagement\Events\CreateConsignment;

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

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Consignment')) && company_setting('Whatsappapi New Consignment')  == true) {

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
