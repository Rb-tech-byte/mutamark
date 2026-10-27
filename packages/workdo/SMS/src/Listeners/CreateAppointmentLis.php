<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\VCard\Events\CreateAppointment;
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
        if (module_is_active('SMS') && company_setting('sms_notification_is', $appointment->created_by, $appointment->workspace) == 'on' && !empty(company_setting('SMS New Appointment', $appointment->created_by, $appointment->workspace)) && company_setting('SMS New Appointment', $appointment->created_by, $appointment->workspace) == true) {

            $to = User::find($appointment->created_by)->mobile_no;
            if (!empty($to)) {
                $business_name = \Workdo\VCard\Entities\Business::where('id', $appointment->business_id)->pluck('title')->first();

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
