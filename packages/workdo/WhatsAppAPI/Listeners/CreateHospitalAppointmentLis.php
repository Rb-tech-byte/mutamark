<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\HospitalManagement\Events\CreateHospitalAppointment;
use Modules\HospitalManagement\Entities\Patient;
use Modules\HospitalManagement\Entities\Doctor;
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

        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Hospital Appointment')) && company_setting('Whatsappapi New Hospital Appointment')  == true) {

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
