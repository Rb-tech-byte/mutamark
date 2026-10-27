<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\PropertyManagement\Events\CreateProperty;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Property')) && company_setting('Whatsappapi New Property')  == true) {

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
