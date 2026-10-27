<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\Fleet\Entities\Maintenance;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Fleet\Events\CreateMaintenances;

class CreateMaintenancesLis
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
    public function handle(CreateMaintenances $event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Maintenance')) && company_setting('Whatsappapi New Maintenance')  == true) {

            $maintenances = $event->Maintenances;
            $request = $event->request;
            $maintenance = Maintenance::find($maintenances->service_for);
            $emp = User::find($request->service_for);

            if (!empty($emp->mobile_no)) {
                $uArr = [
                    'user_name'=>$maintenance->Employee->name,
                ];
                SendMsg::SendMsgs($emp->mobile_no, $uArr , 'New Maintenance');
            }
        }
    }
}
