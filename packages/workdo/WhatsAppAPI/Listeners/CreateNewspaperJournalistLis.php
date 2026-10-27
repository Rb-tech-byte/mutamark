<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Newspaper\Events\CreateNewspaperJournalist;

class CreateNewspaperJournalistLis
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
    public function handle(CreateNewspaperJournalist $event)
    {
        $user = $event->user;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Journalist')) && company_setting('Whatsappapi New Journalist')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'journalist_name' => $user->name
                ];
                SendMsg::SendMsgs($user->mobile_no, $uArr , 'New Journalist');
            }


        }
    }
}
