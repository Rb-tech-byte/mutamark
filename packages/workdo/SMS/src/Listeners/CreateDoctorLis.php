<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\HospitalManagement\Events\CreateDoctor;
use Workdo\HospitalManagement\Entities\Specialization;
class CreateDoctorLis
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
    public function handle(CreateDoctor $event)
    {
        $doctor = $event->doctor;
        $specialization = Specialization::find($doctor->specialization);
        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Doctor')) && company_setting('SMS New Doctor')  == true) {

            if(!empty($specialization) && !empty($doctor->contact_no))
            {
                $uArr = [
                    'doctor_name' => $doctor->name,
                    'specialization' => $specialization->name
                ];
                SendMsg::SendMsgs($doctor->contact_no , $uArr , 'New Doctor');
            }
        }
    }
}
