<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Timesheet\Events\CreateTimesheet;
use App\Models\User;
class CreateTimesheetLis
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
    public function handle(CreateTimesheet $event)
    {
        $timesheet = $event->timesheet;
        $user = User::find($timesheet->created_by);
        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Timesheet')) && company_setting('SMS New Timesheet')  == true) {
            if(!empty($timesheet) && !empty($user) && !empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name,
                    'type' => $timesheet->type,
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Timesheet');
            }
        }
    }
}
