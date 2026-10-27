<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Employee;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Rotas\Events\CreateAvailability;

class CreateAvailabilityLis
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
    public function handle(CreateAvailability $event)
    {
        $availability = $event->availability;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Availabilitys')) && company_setting('SMS New Availabilitys')  == true) {
            $Assign_user_phone = Employee::where('id', $availability->employee_id)->first();
            if (!empty($Assign_user_phone->phone)) {
                $uArr = [];
                SendMsg::SendMsgs($Assign_user_phone->phone, $uArr , 'New Availabilitys');
            }
        }
    }
}
