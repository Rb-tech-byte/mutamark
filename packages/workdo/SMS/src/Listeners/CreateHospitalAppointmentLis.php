<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\HospitalManagement\Events\CreateHospitalAppointment;
use Workdo\HospitalManagement\Entities\Patient;
use Workdo\HospitalManagement\Entities\Doctor;
use App\Models\User;
class CreateHospitalAppointmentLis
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
    public function handle(CreateHospitalAppointment $event)
    {
        $hospitalappointment = $event->hospitalappointment;
        $patient = Patient::find($hospitalappointment->patient_id);
        $doctor = Doctor::find($hospitalappointment->doctor_id);

        $users = User::whereIn('id' , [$doctor->id , $patient->id])->get();


        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Hospital Appointment')) && company_setting('SMS New Hospital Appointment')  == true) {

            foreach($users as $user)
            {
                if(!empty($patient) && !empty($doctor) && !empty($user->mobile_no))
                {
                    $uArr = [
                        'patient_name' => $patient->name,
                        'doctor_name' => $doctor->name
                    ];
                    SendMsg::SendMsgs($user->mobile_no, $uArr , 'New Hospital Appointment');
                }
        }
        }
    }
}
