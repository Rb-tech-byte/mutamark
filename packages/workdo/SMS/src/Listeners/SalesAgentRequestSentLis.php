<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SalesAgent\Events\SalesAgentRequestSent;
use App\Models\User;
class SalesAgentRequestSentLis
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
    public function handle(SalesAgentRequestSent $event)
    {
        $program = $event->program;

        $user = User::find($program->created_by);
        if (module_is_active('SMS') && !empty(company_setting('SMS Sales Agent Request sent')) && company_setting('SMS Sales Agent Request sent')  == true) {
            if(!empty($program) && !empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $program->name,
                    'user_name' => $user->name
                ];

                SendMsg::SendMsgs($user->mobile_no , $uArr , 'Sales Agent Request sent');
            }


        }
    }
}
