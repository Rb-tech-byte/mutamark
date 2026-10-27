<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ChildcareManagement\Events\CreateChild;

class CreateChildLis
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
    public function handle(CreateChild $event)
    {
        $child = $event->child;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Child')) && company_setting('Whatsappapi New Child')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'child_name' => $child->first_name . ' ' .  $child->last_name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Child');
            }
        }
    }
}
