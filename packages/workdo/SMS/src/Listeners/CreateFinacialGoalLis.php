<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Goal\Events\CreateFinacialGoal;
use Workdo\SMS\Entities\SendMsg;
use App\Models\User;


class CreateFinacialGoalLis
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
    public function handle(CreateFinacialGoal $event)
    {
        $goal = $event->goal;
        $user = User::find($goal->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Financial Goal')) && company_setting('SMS New Financial Goal')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $goal->name,
                    'start_date' => $goal->from,
                    'end_date' => $goal->to
                ];

                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Financial Goal');
            }

        }
    }
}
