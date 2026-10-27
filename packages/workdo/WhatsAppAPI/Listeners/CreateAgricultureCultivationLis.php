<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureCultivation;
use Modules\AgricultureManagement\Entities\AgricultureUser;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Cultivation')) && company_setting('Whatsappapi New Agriculture Cultivation')  == true) {

            $farmer = AgricultureUser::where('id' , $agriculturecultivation->farmer)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();
            $farmer = AgricultureUser::where('id' , $agriculturecultivation->farmer)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();
            
            $to =$farmer->phone;
            if(!empty($agriculturecultivation) && !empty($to))
            {                
                $uArr = [
                    'cultivation_name' => $agriculturecultivation->name,
                    'farmer_name' => $farmer->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Cultivation');
            }
        }
    }
}
