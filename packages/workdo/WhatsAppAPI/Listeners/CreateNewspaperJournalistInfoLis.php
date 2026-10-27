<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Newspaper\Events\CreateNewspaperJournalistInfo;

class CreateNewspaperJournalistInfoLis
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
    public function handle(CreateNewspaperJournalistInfo $event)
    {
        $information = $event->information;
        $to =\Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Journalist Information')) && company_setting('Whatsappapi New Journalist Information')  == true) {

            if(!empty($information) && !empty($to))
            {
                $uArr = [
                    'information' => $information->name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Journalist Information');
            }
        }
    }
}
