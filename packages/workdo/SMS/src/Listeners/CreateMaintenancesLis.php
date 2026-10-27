<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\SMS\Entities\SendMsg;
use App\Models\User;
use Workdo\Fleet\Events\CreateMaintenances;
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
        if (module_is_active('SMS') && !empty(company_setting('SMS New Maintenance')) && company_setting('SMS New Maintenance')  == true) {

            $maintenances = $event->Maintenances;
            $request = $event->request;
            $maintenance = Maintenance::find($maintenances->service_for);
            $emp = User::find($request->service_for);
            if (!empty($emp->mobile_no)) {

                $uArr = [
                    'user_name'=>$emp->name,
                ];
                SendMsg::SendMsgs($emp->mobile_no, $uArr , 'New Maintenance');
            }
        }
    }
}
