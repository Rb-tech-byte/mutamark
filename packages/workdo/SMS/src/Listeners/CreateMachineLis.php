<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\MachineRepairManagement\Events\CreateMachine;
class CreateMachineLis
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
    public function handle(CreateMachine $event)
    {
        $machine = $event->machine;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Machine')) && company_setting('SMS New Machine')  == true) {

            if(!empty($machine) && !empty($to))
            {
                $uArr = [
                    'machine_name' => $machine->name
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Machine');
            }
        }
    }
}
