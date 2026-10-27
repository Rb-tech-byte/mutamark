<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\InnovationCenter\Events\CreateCreativity;
use Workdo\InnovationCenter\Entities\Challenge;
use App\Models\User;

class CreateCreativityLis
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
    public function handle(CreateCreativity $event)
    {
        $creativity = $event->creativity;
        $challenge = Challenge::find($creativity->challenge);
        $user = User::find($creativity->created_by);
        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Creativity')) && company_setting('SMS New Creativity')  == true) {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $creativity->creativity_name,
                    'challenge' => !empty($challenge) ? $challenge->name : '-',
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Creativity');
            }

        }
    }
}
