<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Employee;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Rotas\Events\CreateRota;

class CreateRotaLis
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
    public function handle(CreateRota $event)
    {
      $rota =$event->rotas;
      $user_id = $rota->user_id;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Rota')) && company_setting('SMS New Rota')  == true) {
            $Assign_user_phone = Employee::where('id', $user_id)->first();
            if (!empty($Assign_user_phone->phone)) {
                $uArr = [];
                SendMsg::SendMsgs($Assign_user_phone->phone, $uArr , 'New Rota');

            }
        }
    }
}
