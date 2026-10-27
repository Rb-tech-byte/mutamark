<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\VCard\Events\CreateBusiness;
class CreateBusinessLis
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
    public function handle(CreateBusiness $event)
    {

        if (module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Business')) && company_setting('SMS New Business') == true) {
            $request = $event->request;
            $business = $event->business;
            $to = \Auth::user()->mobile_no;
            if (!empty($to)) {
                $uArr = [
                    'business_name' => $request->business_title
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Business');
            }

        }
    }
}
