<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\FormBuilder\Events\FormBuilderConvertTo;
use Workdo\SMS\Entities\SendMsg;

class FormBuilderConvertToLis
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
    public function handle(FormBuilderConvertTo $event)
    {
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS Convert To Modal')) && company_setting('SMS Convert To Modal')  == true) {

            if(!empty($to))
            {
                $uArr = [];
                SendMsg::SendMsgs($to , $uArr , 'Convert To Modal');
            }

        }
    }
}
