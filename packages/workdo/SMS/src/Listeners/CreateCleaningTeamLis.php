<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CleaningManagement\Events\CreateCleaningTeam;
use App\Models\User;
class CreateCleaningTeamLis
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
    public function handle(CreateCleaningTeam $event)
    {
        $cleaning_team = $event->cleaning_team;
        $users = User::whereIn('id' , explode(',',$cleaning_team->user_id))->get();

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Cleaning Team')) && company_setting('SMS New Cleaning Team')  == true) {

            foreach($users as $user)
            {
                if(!empty($cleaning_team) && !empty($user->mobile_no))
                {
                    $uArr = [
                        'team_name' => $cleaning_team->name
                    ];
                    SendMsg::SendMsgs($user->mobile_no, $uArr , 'New Cleaning Team');
                }
            }
        }
    }
}
