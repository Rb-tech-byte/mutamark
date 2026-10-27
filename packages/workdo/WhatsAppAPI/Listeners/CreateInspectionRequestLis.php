<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\VehicleInspectionManagement\Events\CreateInspectionRequest;
use Modules\VehicleInspectionManagement\Entities\InspectionVehicle;
use Modules\WhatsAppAPI\Entities\SendMsg;

class CreateInspectionRequestLis
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
    public function handle(CreateInspectionRequest $event)
    {
        $inspectionRequest = $event->inspectionRequest;
        $vehicle = InspectionVehicle::find($inspectionRequest->vehicle_id);
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Vehicle Inspection Request')) && company_setting('Whatsappapi New Vehicle Inspection Request')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'user_name' => $inspectionRequest->inspector_name,
                    'vehicle_name' => $vehicle->model
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Vehicle Inspection Request');
            }
        }
    }
}
