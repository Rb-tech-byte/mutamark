<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\TourTravelManagement\Events\CreateTransportType;
use Workdo\SMS\Entities\SendMsg;

class CreateTransportTypeLis
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
    public function handle(CreateTransportType $event)
    {
        $transport_type = $event->transport_type;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Transport Type')) && company_setting('SMS New Transport Type')  == true) {
            if(!empty($transport_type) && !empty($to))
            {
                $uArr = [
                    'name' => $transport_type->transport_type_name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Transport Type');

            }

        }
    }
}
