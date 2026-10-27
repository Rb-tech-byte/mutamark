<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SalesAgent\Events\SalesAgentRequestSent;
use App\Models\User;

class SalesAgentRequestSentLis
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
    public function handle(SalesAgentRequestSent $event)
    {
        $program = $event->program;
        
        $user = User::find($program->created_by);
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Sales Agent Request sent')) && company_setting('Whatsappapi Sales Agent Request sent')  == true) {

            if(!empty($program) && !empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $program->name,
                    'user_name' => $user->name
                ];
    
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'Sales Agent Request sent');
            }


        }
    }
}
