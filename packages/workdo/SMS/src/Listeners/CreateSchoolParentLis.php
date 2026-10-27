<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\School\Events\CreateSchoolParent;

class CreateSchoolParentLis
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
    public function handle(CreateSchoolParent $event)
    {
        $parent = $event->parent;
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Parents')) && company_setting('SMS New Parents')  == true) {

            if(!empty($parent) && !empty($parent->contact))
            {
                $uArr = [
                    'parent_name' => $parent->name
                ];
                SendMsg::SendMsgs($parent->contact, $uArr , 'New Parents');
            }


        }
    }
}
