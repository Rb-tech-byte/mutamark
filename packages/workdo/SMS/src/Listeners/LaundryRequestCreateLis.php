<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\LaundryManagement\Events\LaundryRequestCreate;
use Workdo\LaundryManagement\Entities\LaundryService;
use Workdo\SMS\Entities\SendMsg;


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
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Laundry Request')) && company_setting('SMS New Laundry Request')  == true) {

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
