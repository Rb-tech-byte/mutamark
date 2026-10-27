<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\LaundryManagement\Events\LaundryRequestCreate;
use Modules\LaundryManagement\Entities\LaundryService;

class LaundryRequestCreateLis
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
    public function handle(LaundryRequestCreate $event)
    {
        $laundryrequest = $event->laundryrequest;
        $services = LaundryService::whereIn('id', explode(',', $laundryrequest->services))->get()->pluck('name');
        $service_detail = [];
            if (count($services) > 0) {
                foreach ($services as $datasand) {
                    $service_detail[] = $datasand;
                }
            }
        $services = implode(',', $service_detail);
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Laundry Request')) && company_setting('Whatsappapi New Laundry Request')  == true) {

            if(!empty($laundryrequest->phone))
            {
                $uArr = [
                    'user_name' => $laundryrequest->name,
                    'services' => $services
                ];
                SendMsg::SendMsgs($laundryrequest->phone , $uArr , 'New Laundry Request');
            }


        }
    }
}
