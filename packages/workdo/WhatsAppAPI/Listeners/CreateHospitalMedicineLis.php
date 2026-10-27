<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\HospitalManagement\Events\CreateHospitalMedicine;

class CreateHospitalMedicineLis
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
    public function handle(CreateHospitalMedicine $event)
    {
        $medicine = $event->medicine;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Hospital Medicine')) && company_setting('Whatsappapi New Hospital Medicine')  == true) {

            if(!empty($medicine) && !empty($to))
            {
                $uArr = [
                    'name' => $medicine->name
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Hospital Medicine');
            }
        }
    }
}
