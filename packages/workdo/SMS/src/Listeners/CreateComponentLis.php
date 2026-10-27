<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\CMMS\Events\CreateComponent;
use Workdo\SMS\Entities\SendMsg;

class CreateComponentLis
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
    public function handle(CreateComponent $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Component')) && company_setting('SMS New Component')  == true)
        {
            $request = $event->request;
            $component = $request->name;
            $company = User::find($event->components->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($component) && !empty($to)){
                $uArr = [
                    'component_name' => $component,
                ];

                SendMsg::SendMsgs($to, $uArr , 'New Component');
            }
        }
    }
}
