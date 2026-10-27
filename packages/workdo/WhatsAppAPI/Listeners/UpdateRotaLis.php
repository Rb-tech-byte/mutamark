<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Hrm\Entities\Employee;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Rotas\Events\UpdateRota;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Rotas Time Change')) && company_setting('Whatsappapi Rotas Time Change')  == true)
        {
            $Assign_user_phone = Employee::where('id', $rotas->user_id)->first();
            if (!empty($Assign_user_phone->phone)) {
                if (!empty($Assign_user_phone->phone)) {
                    $uArr = [
                        'start_time' => $start_time,
                        'end_time' => $end_time
                    ];

                    SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Rotas Time Change');
            }
        }
        }
    }
}
