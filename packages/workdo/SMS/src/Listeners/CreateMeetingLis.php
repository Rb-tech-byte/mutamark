<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Sales\Events\CreateMeeting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class CreateMeetingLis

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
    public function handle(CreateMeeting $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Meeting Assigned')) && company_setting('SMS Meeting Assigned')  == true)
        {
            $request = $event->request;
            $Assign_user_phone = User::where('id',$request->user)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'meeting_name' => $request->name
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Meeting Assigned');
            }
        }
    }
}
