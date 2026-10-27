<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Rotas\Events\AddDayoff;

class AddDayoffLis
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
    public function handle(AddDayoff $event)
    {
        $profile = $event->profile;
        $add_dayoff = $event->request;
        $date = $add_dayoff->date;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
         && !empty(company_setting('Whatsappapi Days Off') && company_setting('Whatsappapi Days Off')  == true))
        {
            if (!empty($profile->phone)) {
                $uArr = [
                    'date' => $date
                ];
                SendMsg::SendMsgs($profile->phone, $uArr , 'Days Off');
            }
        }
    }
}
