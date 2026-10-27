<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Appointment\Events\CreateAppointments;

class CreateAppointmentsLis
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
    public function handle(CreateAppointments $event)
    {
       $schedule = $event->schedule;
        if (module_is_active('WhatsAppAPI') && !empty(company_setting('Whatsappapi New Appointment', $schedule->created_by, $schedule->workspace)) && company_setting('Whatsappapi New Appointment', $schedule->created_by, $schedule->workspace) == true) {
            $request = $event->request;
            if (!empty($request->phone)) {
                $uArr = [
                    'appointment_name' => $schedule->name,
                    'date' => $request->date,
                    'time' => $request->start_time,
                    'business_name' => $schedule->appointment->name
                ];
                SendMsg::SendMsgs($request->phone,$uArr,'New Appointment',$schedule->created_by,$schedule->workspace);
            }
        }
    }
}
