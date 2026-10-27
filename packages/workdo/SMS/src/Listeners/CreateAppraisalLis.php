<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Performance\Events\CreateAppraisal;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Entities\Employee;
class CreateAppraisalLis
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
    public function handle(CreateAppraisal $event)
    {
        $appraisal = $event->appraisal;
        $employee = Employee::find($appraisal->employee);
        $to = $employee->phone;

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Appraisal')) && company_setting('SMS New Appraisal')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'employee_name' => !empty($employee) ? $employee->name : '-'
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Appraisal');
            }

        }
    }
}
