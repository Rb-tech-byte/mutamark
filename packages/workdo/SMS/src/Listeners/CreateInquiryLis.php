<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ChildcareManagement\Events\CreateInquiry;
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
        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Inquiry')) && company_setting('SMS New Inquiry')  == true) {

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
