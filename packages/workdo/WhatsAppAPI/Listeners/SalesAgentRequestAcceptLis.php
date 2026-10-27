<?php

namespace Modules\WhatsAppAPI\Listeners;

use Modules\SalesAgent\Events\SalesAgentRequestAccept;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;

class SalesAgentRequestAcceptLis
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
    public function handle(SalesAgentRequestAccept $event)
    {
        $program = $event->program;

        $users = User::whereIn('id', explode(',', $program->sales_agents_applicable))->get();

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Sales Agent Request Accept')) && company_setting('Whatsappapi Sales Agent Request Accept') == true) {

            foreach ($users as $user_no) {
                if (!empty($user_no->mobile_no)) {
                    $uArr = [];
                    SendMsg::SendMsgs($user_no->mobile_no , $uArr, 'Sales Agent Request Accept');
                }
            }

        }
    }
}
