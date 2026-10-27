<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Taskly\Events\CreateMilestone;

class CreateMilestoneLis
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
    public function handle(CreateMilestone $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Milestone')) && company_setting('SMS New Milestone')  == true)
        {
            $to = Auth::user()->mobile_no;

                $uArr = [];

                SendMsg::SendMsgs($to ,$uArr, 'New Milestone');
        }
    }
}
