<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\PropertyManagement\Events\CreateProperty;

class CreatePropertyLis
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
    public function handle(CreateProperty $event)
    {
        $property = $event->property;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Property')) && company_setting('SMS New Property')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'property_name' => $property->name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Property');
            }


        }
    }
}
