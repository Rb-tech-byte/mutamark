<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CMMS\Events\CreateCmmspos;

class CreateCmmsposLis
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
    public function handle(CreateCmmspos $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New POs')) && company_setting('SMS New POs')  == true)
        {
            $request = $event->request;
            $user = User::find($request->user_id);
            $company = User::find($event->Pos->company_id);
            $to=\Auth::user()->mobile_no;
            if(!empty($user) && !empty($to)){
                $uArr = [
                    'user_name' => $user->name,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New POs');
            }
        }
    }
}
