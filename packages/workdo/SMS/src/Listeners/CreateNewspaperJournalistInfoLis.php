<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Newspaper\Events\CreateNewspaperJournalistInfo;

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

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Journalist Information')) && company_setting('SMS New Journalist Information')  == true) {

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
