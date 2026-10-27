<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use App\Events\CreateUser;


class CreateUserLis
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
    public function handle(CreateUser $event)
    {
        $user = $event->user;
        if (module_is_active('SMS') && company_setting('sms_notification_is' , $event->user->company_id) == 'on'  && !empty(company_setting('SMS Create User')) && company_setting('SMS Create User') == true) {
            $uArr = [
                'user_name' => $user->name,
            ];
            $to = $user->mobile_no;

             $response = SendMsg::SendMsgs($to , $uArr , 'Create User');
        }
    }
}
