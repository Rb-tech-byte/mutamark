<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\VCard\Events\BusinessStatus;

class StatusChangeBusinessLis
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
    public function handle(BusinessStatus $event)
    {
        $status = $event->status;
        if (module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS Business Status Updated')) && company_setting('SMS Business Status Updated') == true) {
            $to = \Auth::user()->mobile_no;
            if (!empty($to)) {
                $uArr = [];
                SendMsg::SendMsgs($to , $uArr , 'Business Status Updated');
            }
        }
    }
}
