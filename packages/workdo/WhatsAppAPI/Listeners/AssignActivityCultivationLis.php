<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\AssignActivityCultivation;
use Modules\AgricultureManagement\Entities\AgricultureActivities;
use Illuminate\Support\Facades\Auth;

class AssignActivityCultivationLis
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
    public function handle(AssignActivityCultivation $event)
    {
        $agriculturecultivation = $event->agriculturecultivation;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Assign Activity Cultivation')) && company_setting('Whatsappapi Assign Activity Cultivation')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agriculturecultivation) && !empty($to)) 
            {
                $activities = AgricultureActivities::whereIn('id' ,json_decode($agriculturecultivation->activites))->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->get()->pluck('name', 'id')->toArray();

                $uArr = [
                    'activity' => implode(',',$activities),
                    'cultivation' => $agriculturecultivation->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'Assign Activity Cultivation');
            }
        }
    }
}
