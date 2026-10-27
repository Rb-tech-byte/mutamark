<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ToDo\Events\CompleteToDo;
use App\Models\User;

class CompleteToDoLis
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
    public function handle(CompleteToDo $event)
    {
        $toDo = $event->toDo;
        $users = User::whereIn('id', explode(',', $toDo->assigned_to))->get();

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS Complete To Do')) && company_setting('SMS Complete To Do')  == true) {

            foreach($users as $user)
            {
                if(!empty($user->mobile_no))
                {
                    $uArr = [
                        'user_name' => $user->name
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'Complete To Do');
                }
            }
        }
    }
}
