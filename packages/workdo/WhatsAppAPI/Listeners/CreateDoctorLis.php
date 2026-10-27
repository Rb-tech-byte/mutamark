<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\HospitalManagement\Events\CreateDoctor;
use Modules\HospitalManagement\Entities\Specialization;

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
        if (module_is_active('WhatsAppAPI')  && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Doctor')) && company_setting('Whatsappapi New Doctor')  == true) {

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
