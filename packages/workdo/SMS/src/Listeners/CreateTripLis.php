<?php

namespace Workdo\SMS\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Events\CreateTrip;

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
        if (module_is_active('SMS') && !empty(company_setting('SMS New Trip')) && company_setting('SMS New Trip')  == true) {
            $request = $event->request;
            $employee = User::where('id', '=', $request->employee_id)->first();
            if (!empty($employee->mobile_no)) {
                $uArr = [
                    'purpose_of_visit' => $request->purpose_of_visit,
                    'place_of_visit' => $request->place_of_visit ,
                    'user_name' => $employee->name ?? '-',
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ];
                SendMsg::SendMsgs($employee->mobile_no, $uArr , 'New Trip');
            }
        }
    }
}
