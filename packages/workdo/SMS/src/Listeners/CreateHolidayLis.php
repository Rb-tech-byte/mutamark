<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Events\CreateHolidays;

class CreateHolidayLis
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
    public function handle(CreateHolidays $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Holidays')) && company_setting('SMS New Holidays')  == true)
        {
            $request = $event->request;
            $to = \Auth::user()->mobile_no;
            if(!empty($to)){

                $uArr = [
                    'name' => $request->occasion,
                    'start_date'=>$request->start_date,
                    'end_date' => $request->end_date
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Holidays');
            }
        }
    }
}
