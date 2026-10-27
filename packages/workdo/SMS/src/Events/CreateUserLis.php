<?php

namespace Workdo\SMS\Events;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\CreateUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;


class CreateUserLis
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function handle(CreateUser $event)
    {
        $user = $event->user;
        if (module_is_active('sms') && company_setting('sms_notification_is' , $event->user->company_id) == 'on'  && !empty(company_setting('SMS Create User')) && company_setting('SMS Create User') == true) {

            $uArr = [
                'user_name' => $user->name,
            ];
            $to=\Auth::user()->mobile_no;

             $response = SendMsg::SendMsgs($to , $uArr , 'Create User');
        }
    }
}
