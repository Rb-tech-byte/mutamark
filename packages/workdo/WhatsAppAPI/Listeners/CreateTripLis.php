<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Hrm\Events\CreateTrip;

class CreateTripLis
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
    public function handle(CreateTrip $event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Trip')) && company_setting('Whatsappapi New Trip')  == true)
        {
            $request = $event->request;
            $employee = \Modules\Hrm\Entities\Employee::where('id', '=', $request->employee_id)->first();
            if (!empty($employee->phone)) {
                $uArr = [
                    'purpose_of_visit' => $request->purpose_of_visit,
                    'place_of_visit' => $request->place_of_visit ,
                    'user_name' => $employee->name,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ];
                SendMsg::SendMsgs($employee->phone, $uArr , 'New Trip');
            }
        }
    }
}
