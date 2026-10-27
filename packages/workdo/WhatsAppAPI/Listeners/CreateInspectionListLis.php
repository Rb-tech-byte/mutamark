<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VehicleInspectionManagement\Events\CreateInspectionList;

class CreateInspectionListLis
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
     * Handle the event.ac
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CreateInspectionList $event)
    {
        $inspectionList = $event->inspectionList;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Inspection List')) && company_setting('Whatsappapi New Inspection List')  == true) {
            if(!empty($inspectionList) && !empty($to))
            {
                $uArr = [
                    'name' => $inspectionList->name,
                    'hours' => $inspectionList->period
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Inspection List');
            }
        }
    }
}
