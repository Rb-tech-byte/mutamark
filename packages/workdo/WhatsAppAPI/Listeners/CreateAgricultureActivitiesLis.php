<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureActivities;
use Modules\AgricultureManagement\Entities\AgricultureCrop;
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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Activity')) && company_setting('Whatsappapi New Agriculture Activity')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agriculture_activity) && !empty($to))
            {
                $crop = AgricultureCrop::where('id' , $agriculture_activity->crop)->where('workspace',getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'activity_name' => $agriculture_activity->name,
                    'crop_name' => $crop->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Activity');
            }
        }
    }
}
