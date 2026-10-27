<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SalesAgent\Events\SalesAgentProgramCreate;
use App\Models\User;

class SalesAgentProgramCreateLis
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
    public function handle(SalesAgentProgramCreate $event)
    {
        $program = $event->program;
        $user = User::find($program->created_by);

        $users = User::whereIn('id' , explode(',' , $program->sales_agents_applicable))->get();
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Program')) && company_setting('Whatsappapi New Program')  == true) {

            foreach($users as $user_no)
            {
                if(!empty($program) && !empty($user) && !empty($user_no->mobile_no))
                {
                    $uArr = [
                        'program_name' => $program->name,
                        'user_name'    => $user->name,
                        'start_date'   => $program->from_date,
                        'end_date'     => $program->to_date
                    ];
                    SendMsg::SendMsgs($user_no->mobile_no , $uArr , 'New Program');    
                }
            }
        }
    }
}
