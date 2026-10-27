<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SalesAgent\Events\SalesAgentRequestReject;
use App\Models\User;

class SalesAgentRequestRejectLis
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
    public function handle(SalesAgentRequestReject $event)
    {
        $program = $event->program;

        $users = User::whereIn('id', explode(',', $program->sales_agents_applicable))->get();

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Sales Agent Request Reject')) && company_setting('Whatsappapi Sales Agent Request Reject')  == true) {

            foreach ($users as $user_no) {
                if (!empty($user_no->mobile_no)) {
                    $uArr = [];
                    SendMsg::SendMsgs($user_no->mobile_no , $uArr, 'Sales Agent Request Reject');
                }
            }

        }
    }
}
