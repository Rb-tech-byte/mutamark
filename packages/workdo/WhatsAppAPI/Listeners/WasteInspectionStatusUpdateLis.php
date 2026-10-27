<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\WasteManagement\Events\WasteInspectionStatusUpdate;

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
        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Update Waste Inspection Status')) && company_setting('Whatsappapi Update Waste Inspection Status')  == true) {

            if(!empty($WasteCollection->phone))
            $uArr = [
                'user_name' => $WasteCollection->name,
                'status' => $status
            ];
            SendMsg::SendMsgs($WasteCollection->phone , $uArr , 'Update Waste Inspection Status');

        }
    }
}
