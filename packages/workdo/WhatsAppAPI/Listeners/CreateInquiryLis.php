<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ChildcareManagement\Events\CreateInquiry;

class CreateInquiryLis
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
    public function handle(CreateInquiry $event)
    {
        $inquiry = $event->inquiry;
        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Inquiry')) && company_setting('Whatsappapi New Inquiry')  == true) {

            if(!empty($inquiry) && !empty($inquiry->contact_number))
            {
                $uArr = [
                    'child_name' => $inquiry->child_first_name,
                    'parent_name' => $inquiry->parent_first_name
                ];
                SendMsg::SendMsgs($inquiry->contact_number, $uArr , 'New Inquiry');
            }
        }
    }
}
