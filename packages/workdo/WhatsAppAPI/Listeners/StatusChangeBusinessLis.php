<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VCard\Events\BusinessStatus;

class StatusChangeBusinessLis
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
    public function handle(BusinessStatus $event)
    {
        $status = $event->status;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Business Status Updated')) && company_setting('Whatsappapi Business Status Updated') == true) {
            $to = \Auth::user()->mobile_no;
            if (!empty($to)) {
                $uArr = [];

                SendMsg::SendMsgs($to , $uArr , 'Business Status Updated');
            }
        }
    }
}
