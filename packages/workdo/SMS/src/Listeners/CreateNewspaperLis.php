<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Newspaper\Events\CreateNewspaper;
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

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Newspaper')) && company_setting('SMS New Newspaper')  == true) {

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
