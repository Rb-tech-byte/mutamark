<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\School\Events\CreateAdmission;

class CreateAdmissionLis
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
    public function handle(CreateAdmission $event)
    {
        $admission = $event->admission;
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Addmissions')) && company_setting('SMS New Addmissions')  == true) {

            if(!empty($admission) && !empty($admission->phone))
            {
                $uArr = [
                    'student_name' => $admission->student_name
                ];
                SendMsg::SendMsgs($admission->phone , $uArr , 'New Addmissions');
            }
        }
    }
}
