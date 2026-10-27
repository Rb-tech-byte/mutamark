<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Newspaper\Events\CreateNewspaperJournalist;
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
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Journalist')) && company_setting('SMS New Journalist')  == true) {

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
