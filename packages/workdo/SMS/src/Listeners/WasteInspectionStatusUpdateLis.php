<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\WasteManagement\Events\WasteInspectionStatusUpdate;

class WasteInspectionStatusUpdateLis
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
    public function handle(WasteInspectionStatusUpdate $event)
    {
        $WasteCollection = $event->WasteCollection;
        if($WasteCollection->inspection_status == 1)
        {
            $status = 'Won';
        }
        else
        {
            $status = 'Rejected';
        }

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS Update Waste Inspection Status')) && company_setting('SMS Update Waste Inspection Status')  == true) {

            if(!empty($WasteCollection->phone))
            $uArr = [
                'user_name' => $WasteCollection->name,
                'status' => $status
            ];
            SendMsg::SendMsgs($WasteCollection->phone , $uArr , 'Update Waste Inspection Status');

        }
    }
}
