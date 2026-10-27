<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateMaintenance;
use Modules\FixEquipment\Entities\FixAsset;
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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Maintenance')) && company_setting('Whatsappapi New Maintenance')  == true) {

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
