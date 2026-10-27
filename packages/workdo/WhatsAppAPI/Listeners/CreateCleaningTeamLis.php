<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CleaningManagement\Events\CreateCleaningTeam;
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

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Cleaning Team')) && company_setting('Whatsappapi New Cleaning Team')  == true) {

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
