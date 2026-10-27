<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\AssignActivityCultivation;
use Workdo\AgricultureManagement\Entities\AgricultureActivities;
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
        if (module_is_active('SMS') && !empty(company_setting('SMS Assign Activity Cultivation')) && company_setting('SMS Assign Activity Cultivation')  == true) {

            if(!empty($agriculturecultivation))
            {
                $activities = AgricultureActivities::whereIn('id' ,json_decode($agriculturecultivation->activites))->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->get()->pluck('name', 'id')->toArray();

                $uArr = [
                    'activity' => implode(',',$activities),
                    'cultivation' => $agriculturecultivation->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'Assign Activity Cultivation');
            }
        }
    }
}
