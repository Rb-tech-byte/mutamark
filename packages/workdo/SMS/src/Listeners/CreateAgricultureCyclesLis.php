<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureCycles;
use Illuminate\Support\Facades\Auth;

class CreateAgricultureCyclesLis
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
    public function handle(CreateAgricultureCycles $event)
    {
        $agriculturecycles = $event->agriculturecycles;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture cycle')) && company_setting('SMS New Agriculture cycle')  == true) {

            if(!empty($agriculturecycles))
            {
                $uArr = [
                    'cycle_name' => $agriculturecycles->name,
                ];
                $to = Auth::user()->mobile_no;

                SendMsg::SendMsgs($to,$uArr , 'New Agriculture cycle');
            }
        }
    }
}
