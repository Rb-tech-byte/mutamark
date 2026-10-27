<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\Hrm\Entities\Employee;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Rotas\Events\DestroyRota;

class DestroyRotaLis
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
    public function handle(DestroyRota $event)
    {
        $rotas = $event->rota;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Cancle Rotas')) && company_setting('Whatsappapi Cancle Rotas')  == true)
        {
            $Assign_user_phone = Employee::where('id', $rotas->user_id)->first();
            if (!empty($Assign_user_phone->phone)) {
                $uArr = [];
                SendMsg::SendMsgs($Assign_user_phone->phone, $uArr , 'Cancle Rotas');

            }
        }
    }
}
