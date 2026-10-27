<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Appointment\Events\CreateAppointments;

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
        $schedule   = $event->schedule;
        if(module_is_active('SMS') && !empty(company_setting('SMS New Appointment',$schedule->created_by, $schedule->workspace)) && company_setting('SMS New Appointment',$schedule->created_by, $schedule->workspace)  == true)
        {
            $request = $event->request;
            if(!empty($request->phone)){
                $msg = $schedule->appointment->name . ' ' . __("appointment created for") . ' ' . $schedule->name . ' ' . __("from") . ' ' . $request->date . '.';
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
