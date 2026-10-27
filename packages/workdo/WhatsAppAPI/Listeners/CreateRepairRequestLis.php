<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\MachineRepairManagement\Events\CreateRepairRequest;
use Modules\MachineRepairManagement\Entities\Machine;
use Modules\WhatsAppAPI\Entities\SendMsg;

class CreateRepairRequestLis
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
    public function handle(CreateRepairRequest $event)
    {
        $repair_request = $event->repair_request;
        $machine = Machine::find($repair_request->machine_id);
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Repair Request')) && company_setting('Whatsappapi New Repair Request')  == true) {

            if(!empty($machine) && !empty($to))
            {
                $uArr = [
                    'machine_name' => $machine->name
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Repair Request');
            }
        }
    }
}
