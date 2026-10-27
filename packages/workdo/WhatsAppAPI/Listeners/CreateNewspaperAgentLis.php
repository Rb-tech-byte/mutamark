<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Newspaper\Events\CreateNewspaperAgent;
use Modules\WhatsAppAPI\Entities\SendMsg;

class CreateNewspaperAgentLis
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
    public function handle(CreateNewspaperAgent $event)
    {
        $user = $event->user;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Agent')) && company_setting('Whatsappapi New Agent')  == true) {

            if(!empty($user) && !empty($user->mobile_no))
            {
                $uArr = [
                    'agent_name' => $user->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Agent');
            }


        }
    }
}
