<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Recruitment\Events\CreateInterviewSchedule;
class CreateInterviewScheduleLis
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
    public function handle(CreateInterviewSchedule $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Interview Schedule')) && company_setting('SMS Interview Schedule')  == true)
        {
            $request = $event->request;
            $schedule = $event->schedule;
            $employee = \Workdo\Hrm\Entities\Employee::where('id',$request->employee)->first();
            if(!empty($employee->phone)){
                $uArr = [
                    'user_name' => $schedule->users->name,
                    'application' => $schedule->applications->name
                ];
                SendMsg::SendMsgs($employee->phone, $uArr , 'Interview Schedule');
            }
        }
    }
}
