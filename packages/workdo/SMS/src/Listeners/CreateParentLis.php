<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ChildcareManagement\Events\CreateParent;
class CreateParentLis
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
    public function handle(CreateParent $event)
    {
        $parent = $event->parent;
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Parent')) && company_setting('SMS New Parent')  == true) {

            if(!empty($parent->contact_number))
            {
                $uArr = [
                    'parent_name' => $parent->first_name . ' '.$parent->last_name
                ];
                SendMsg::SendMsgs($parent->contact_number, $uArr , 'New Parent');
            }
        }
    }
}
