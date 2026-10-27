<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\TourTravelManagement\Events\CreateSeason;

class CreateSeasonLis
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
    public function handle(CreateSeason $event)
    {
        $tourseason = $event->tourseason;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Season')) && company_setting('Whatsappapi New Season')  == true) {

            if(!empty($tourseason) && !empty($to))
            {
                $uArr = [
                    'season_name' => $tourseason->season_name,
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Season');
            }


        }
    }
}
