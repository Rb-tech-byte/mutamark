<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\InnovationCenter\Events\CreateChallenge;
use Workdo\SMS\Entities\SendMsg;
use App\Models\User;
class CreateChallengeLis
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
    public function handle(CreateChallenge $event)
    {
        $Challenges = $event->Challenges;
        $user = User::find($Challenges->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Challenge')) && company_setting('SMS New Challenge')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $Challenges->name,
                    'position' => $Challenges->position
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Challenge');
            }

        }
    }
}
