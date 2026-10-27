<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Newspaper\Events\CreateNewspaper;

class CreateNewspaperLis
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
    public function handle(CreateNewspaper $event)
    {
        $newspaper = $event->newspaper;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Newspaper')) && company_setting('Whatsappapi New Newspaper')  == true) {

            if(!empty($newspaper) && !empty($to))
            {
                $uArr = [
                    'newspaper_name' => $newspaper->name
                ];
                SendMsg::SendMsgs($to ,$uArr , 'New Newspaper');
            }


        }
    }
}
