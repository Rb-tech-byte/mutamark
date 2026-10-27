<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\TourTravelManagement\Events\CreateTransportType;
use Modules\WhatsAppAPI\Entities\SendMsg;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Transport Type')) && company_setting('Whatsappapi New Transport Type')  == true) {

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
