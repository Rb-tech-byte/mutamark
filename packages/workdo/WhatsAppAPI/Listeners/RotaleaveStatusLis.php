<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Rotas\Events\RotaleaveStatus;

class RotaleaveStatusLis
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
    public function handle(RotaleaveStatus $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi RotaLeave Approve/Reject')) && company_setting('Whatsappapi RotaLeave Approve/Reject')  == true)
        {
            $leave = $event->leave;
            $employee = \Modules\Rotas\Entities\Employee::where('id', '=', $leave->employee_id)->first();
            if(!empty($employee->phone)){
                $uArr = [];
                SendMsg::SendMsgs($employee->phone, $uArr , 'RotaLeave Approve/Reject');
            }
        }
    }
}
