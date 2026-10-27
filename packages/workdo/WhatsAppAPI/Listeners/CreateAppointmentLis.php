<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VCard\Events\CreateAppointment;

class CreateAppointmentLis
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
    public function handle(CreateAppointment $event)
    {
        $request = $event->request;
        $appointment = $event->appointment;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is', $appointment->created_by, $appointment->workspace) == 'on' && !empty(company_setting('Whatsappapi New Appointment', $appointment->created_by, $appointment->workspace)) && company_setting('Whatsappapi New Appointment', $appointment->created_by, $appointment->workspace) == true) {

            $to = \Auth::user()->mobile_no;

            if (!empty($to)) {
                $business_name = \Modules\VCard\Entities\Business::where('id', $appointment->business_id)->pluck('title')->first();
                $uArr = [
                    'appointment_name' => $request->name,
                    'date'=> $request->date,
                    'time'=> $request->time,
                    'business_name'=>$business_name,
                ];
                SendMsg::SendMsgs($to, $uArr, 'New Appointment' ,$appointment->created_by, $appointment->workspace);
            }

        }
    }
}
