<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureActivities;
use Workdo\AgricultureManagement\Entities\AgricultureCrop;
use Illuminate\Support\Facades\Auth;

class CreateAgricultureActivitiesLis
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
    public function handle(CreateAgricultureActivities $event)
    {
        $agriculture_activity = $event->agriculture_activity;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Activity')) && company_setting('SMS New Agriculture Activity')  == true) {

            if(!empty($agriculture_activity))
            {
                $crop = AgricultureCrop::where('id' , $agriculture_activity->crop)->where('workspace',getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'activity_name' => $agriculture_activity->name,
                    'crop_name' => $crop->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Activity');
            }
        }
    }
}
