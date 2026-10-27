<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Hrm\Entities\Employee;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Rotas\Events\UpdateRota;

class UpdateRotaLis
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
    public function handle(UpdateRota $event)
    {
        $rotas = $event->rota;
        $start_time = $rotas->start_time;
        $end_time = $rotas->end_time;

        if (module_is_active('SMS') && !empty(company_setting('SMS Rotas Time Change')) && company_setting('SMS Rotas Time Change')  == true) {
            $Assign_user_phone = Employee::where('id', $rotas->user_id)->first();
            if (!empty($Assign_user_phone->phone)) {
             $uArr = [
                'start_time' => $start_time,
                'end_time' => $end_time
            ];

            SendMsg::SendMsgs($Assign_user_phone->phone, $uArr , 'Rotas Time Change');
            }
        }
    }
}
