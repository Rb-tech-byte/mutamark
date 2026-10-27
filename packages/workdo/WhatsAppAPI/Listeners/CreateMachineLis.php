<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\MachineRepairManagement\Events\CreateMachine;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Machine')) && company_setting('Whatsappapi New Machine')  == true) {

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
