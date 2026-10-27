<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ChildcareManagement\Events\CreateChild;
class CreateChildLis
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
    public function handle(CreateChild $event)
    {
        $child = $event->child;
        $to = \Auth::user()->mobile_no;
        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Child')) && company_setting('SMS New Child')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'child_name' => $child->first_name . ' ' .  $child->last_name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Child');
            }
        }
    }
}
