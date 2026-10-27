<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\School\Events\CreateSchoolEmployee;

class CreateSchoolEmployeeLis
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
    public function handle(CreateSchoolEmployee $event)
    {
        $employee = $event->employee;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Teacher')) && company_setting('SMS New Teacher')  == true) {
            if(!empty($employee) && !empty($employee->phone))
            {
                $uArr = [
                    'teacher_name' => $employee->name
                ];
                SendMsg::SendMsgs($employee->phone , $uArr , 'New Teacher');
            }
        }
    }
}
