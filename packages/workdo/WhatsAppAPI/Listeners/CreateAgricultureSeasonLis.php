<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureSeason;
use Modules\AgricultureManagement\Entities\AgricultureSeasonType;
use Illuminate\Support\Facades\Auth;


class CreateAgricultureSeasonLis
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
    public function handle(CreateAgricultureSeason $event)
    {
        $agricultureseason = $event->agricultureseason;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Season')) && company_setting('Whatsappapi New Agriculture Season')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agricultureseason) && !empty($to))
            {
            $season = AgricultureSeasonType::where('id',$agricultureseason->season)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'season_name' => $agricultureseason->name,
                    'season' => $season->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Season');
            }
        }
    }
}
