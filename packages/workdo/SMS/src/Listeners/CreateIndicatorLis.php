<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Performance\Events\CreateIndicator;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Entities\Branch;
use App\Models\User;

class CreateIndicatorLis
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
    public function handle(CreateIndicator $event)
    {
        $indicator = $event->indicator;
        $branch = Branch::find($indicator->branch);
        $user = User::find($branch->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Indicator')) && company_setting('SMS New Indicator')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'branch_name' => !empty($branch) ? $branch->name : '-'
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Indicator');
            }

        }
    }
}
