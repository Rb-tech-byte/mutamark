<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateMaintenance;
use Workdo\FixEquipment\Entities\FixAsset;
use App\Models\User;

class CreateMaintenanceLis
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
    public function handle(CreateMaintenance $event)
    {
        $maintenance = $event->maintenance;
        $asset = FixAsset::find($maintenance->asset);

        $user = User::find($maintenance->created_by);

        if (module_is_active('SMS') && !empty(company_setting('SMS New Maintenance')) && company_setting('SMS New Maintenance')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name'  => $maintenance->maintenance_type,
                    'asset' => $asset->title,
                    'date'  => $maintenance->maintenance_date
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Maintenance');
            }
        }
    }
}
