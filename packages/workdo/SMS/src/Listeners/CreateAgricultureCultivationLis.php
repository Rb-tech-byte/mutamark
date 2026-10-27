<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureCultivation;
use Workdo\AgricultureManagement\Entities\AgricultureUser;

class CreateAgricultureCultivationLis
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
    public function handle(CreateAgricultureCultivation $event)
    {
        $agriculturecultivation = $event->agriculturecultivation;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Cultivation')) && company_setting('SMS New Agriculture Cultivation')  == true) {

            if(!empty($agriculturecultivation))
            {
                $farmer = AgricultureUser::where('id' , $agriculturecultivation->farmer)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();
                $farmer = AgricultureUser::where('id' , $agriculturecultivation->farmer)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'cultivation_name' => $agriculturecultivation->name,
                    'farmer_name' => $farmer->name,
                ];
                $to =$farmer->phone;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Cultivation');
            }
        }
    }
}
