<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\HospitalManagement\Events\CreateHospitalMedicine;
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

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Hospital Medicine')) && company_setting('SMS New Hospital Medicine')  == true) {

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
