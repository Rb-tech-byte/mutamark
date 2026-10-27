<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VehicleInspectionManagement\Events\CreateInspectionVehicle;

class CreateInspectionVehicleLis
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
    public function handle(CreateInspectionVehicle $event)
    {
        $inspectionVehicle = $event->inspectionVehicle;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Inspection Vehicle')) && company_setting('Whatsappapi New Inspection Vehicle')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'vehicle_name' => $inspectionVehicle->model
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Inspection Vehicle');    
            }
            
        }
    }
}
