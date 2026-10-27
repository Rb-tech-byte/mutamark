<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Recruitment\Events\ConvertToEmployee;

class ConvertToEmployeeLis
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
    public function handle(ConvertToEmployee $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on'
        && !empty(company_setting('Whatsappapi Convert To Employee')) && company_setting('Whatsappapi Convert To Employee')  == true)
        {
            $request = $event->request;
            if(!empty($request['phone'])){
                $uArr = [];
                SendMsg::SendMsgs($request['phone'], $uArr , 'Convert to Employee');
            }
        }
    }
}
