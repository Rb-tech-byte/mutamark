<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Newspaper\Events\CreateNewspaperAgent;
use Workdo\SMS\Entities\SendMsg;
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
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Agent')) && company_setting('SMS New Agent')  == true) {

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
