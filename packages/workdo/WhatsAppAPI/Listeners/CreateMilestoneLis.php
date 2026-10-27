<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Taskly\Events\CreateMilestone;

class CreateMilestoneLis
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
    public function handle(CreateMilestone $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Milestone')) && company_setting('Whatsappapi New Milestone')  == true)
        $to = Auth::user()->mobile_no;
        {
            if(!empty($to))
            {
                $uArr = [];

                SendMsg::SendMsgs($to ,$uArr, 'New Milestone');
            }

        }
    }
}
