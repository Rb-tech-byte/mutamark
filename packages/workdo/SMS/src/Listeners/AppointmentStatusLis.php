<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Appointment\Events\AppointmentStatus;

class AppointmentStatusLis
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
    public function handle(AppointmentStatus $event)
    {

        if(module_is_active('SMS') && !empty(company_setting('SMS Appointment Status')) && company_setting('SMS Appointment Status')  == true)
        {
            $request = $event->request;
            $schedule = $event->schedule;
            if(!empty($schedule->phone)){
                $uArr = [
                    'appointment_name'=>$schedule->appointment->name,
                    'status'=>$schedule->status,
                ];
                SendMsg::SendMsgs($schedule->phone, $uArr,'Appointment Status', $schedule->created_by, $schedule->workspace);
            }
        }
    }
}
