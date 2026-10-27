<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Rotas\Events\RotaleaveStatus;
class RotaleaveStatusLis
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
    public function handle(RotaleaveStatus $event)
    {

        if(module_is_active('SMS') && !empty(company_setting('SMS RotaLeave Approve/Reject')) && company_setting('SMS RotaLeave Approve/Reject')  == true)
        {
            $leave = $event->leave;
            $employee = \Workdo\Rotas\Entities\Employee::where('id', '=', $leave->employee_id)->first();
            if(!empty($employee->phone)){
                $uArr = [];
                SendMsg::SendMsgs($employee->phone, $uArr , 'RotaLeave Approve/Reject');
            }
        }
    }
}
