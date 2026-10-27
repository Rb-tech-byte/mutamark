<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\HospitalManagement\Events\CreatePatient;
use Modules\WhatsAppAPI\Entities\SendMsg;

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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Patient')) && company_setting('Whatsappapi New Patient')  == true) {

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
