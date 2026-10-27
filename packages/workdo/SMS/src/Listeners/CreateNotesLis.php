<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Notes\Events\CreateNotes;
use App\Models\User;
class CreateNotesLis
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
    public function handle(CreateNotes $event)
    {
        $note = $event->note;
        $users = User::whereIn('id', explode(',', $note->assign_to))->get();

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Note')) && company_setting('SMS New Note')  == true) {

            foreach($users as $user)
            {
                if(!empty($user->mobile_no))
                {
                    $uArr = [
                        'note_type' => $note->type,
                        'user_name' => $user->name
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Note');
                }

            }
        }

    }
}
