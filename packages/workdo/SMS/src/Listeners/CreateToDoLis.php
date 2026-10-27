<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ToDo\Events\CreateToDo;
use App\Models\User;
class CreateToDoLis
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
    public function handle(CreateToDo $event)
    {
        $toDo = $event->toDo;
        $users = User::whereIn('id' , explode(',' , $toDo->assigned_to))->get();

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New To Do')) && company_setting('SMS New To Do')  == true) {

            foreach($users as $user)
            {
                if(!empty($user->mobile_no))
                {
                    $uArr = [
                        'name' => $toDo->title,
                        'module' => $toDo->sub_module
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'New To Do');
                }

            }
        }
    }
}
