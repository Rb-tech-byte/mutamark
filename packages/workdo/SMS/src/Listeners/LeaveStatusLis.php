<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Events\LeaveStatus;

class LeaveStatusLis
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
    public function handle(LeaveStatus $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Leave Approve/Reject')) && company_setting('SMS Leave Approve/Reject')  == true)
        {
            $leave = $event->leave;
            $employee = \Workdo\Hrm\Entities\Employee::where('id', '=', $leave->employee_id)->first();
            if(!empty($employee->phone)){

                $uArr = [
                    'status' => $leave->status
                ];
                SendMsg::SendMsgs($employee->phone, $uArr , 'Leave Approve/Reject');
            }
        }
    }
}
