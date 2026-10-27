<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CMMS\Entities\Component;
use Workdo\CMMS\Events\CreateWorkrequest;

class CreateWorkrequestLis
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
    public function handle(CreateWorkrequest $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is' , $event->workorder->company_id) == 'on' && !empty(company_setting('SMS Work Order Request' , $event->workorder->company_id)) && company_setting('SMS Work Order Request' , $event->workorder->company_id)  == true)
        {
            $request = $event->request;
            $user = $request->user_name;
            $component = Component::find($request->components_id);
            if(\Auth::check())
            {
                $to=\Auth::user()->mobile_no;
            }
            else{
                $to = User::find($component->company_id);
            }
            if(!empty($component) && !empty($to)){
                $uArr = [
                    'component_name' => $component->name,
                    'user_name' => $user,
                ];
                SendMsg::SendMsgs($to, $uArr , 'Work Order Request' ,$component->company_id ,$component->workspace );
            }
        }
    }
}
