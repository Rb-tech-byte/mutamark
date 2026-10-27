<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\HospitalManagement\Events\CreatePatient;
use Workdo\SMS\Entities\SendMsg;
class CreatePatientLis
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
    public function handle(CreatePatient $event)
    {
        $patient = $event->patient;
        if (module_is_active('SMS') && company_setting('sms _notification_is')=='on' && !empty(company_setting('SMS New Patient')) && company_setting('SMS New Patient')  == true) {

            if(!empty($patient) && !empty($patient->contact_no))
            {
                $uArr = [
                    'patient_name' => $patient->name
                ];
                SendMsg::SendMsgs($patient->contact_no , $uArr , 'New Patient');
            }
        }
    }
}
