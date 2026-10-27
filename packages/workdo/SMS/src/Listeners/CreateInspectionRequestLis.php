<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\VehicleInspectionManagement\Events\CreateInspectionRequest;
use Workdo\VehicleInspectionManagement\Entities\InspectionVehicle;
use Workdo\SMS\Entities\SendMsg;

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

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Vehicle Inspection Request')) && company_setting('SMS New Vehicle Inspection Request')  == true) {

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
