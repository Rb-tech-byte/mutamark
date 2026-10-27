<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Performance\Events\CreateGoalTracking;
use Workdo\Hrm\Entities\Branch;
use App\Models\User;

class CreateGoalTrackingLis
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
    public function handle(CreateGoalTracking $event)
    {
        $goalTracking = $event->goalTracking;
        $branch = Branch::find($goalTracking->branch);
        $user = User::find($branch->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Goal Tracking')) && company_setting('SMS New Goal Tracking')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'start_date' => $goalTracking->start_date,
                    'end_date' => $goalTracking->end_date,
                    'branch_name' => !empty($branch) ? $branch->name : '-'
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Goal Tracking');
            }

        }
    }
}
