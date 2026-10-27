<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateAsset;
use Modules\FixEquipment\Entities\EquipmentLocation;
use App\Models\User;

class CreateAssetLis
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
    public function handle(CreateAsset $event)
    {
        $asset = $event->asset;
        $supplier = User::find($asset->supplier);
        $location = EquipmentLocation::find($asset->location);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Asset')) && company_setting('Whatsappapi New Asset')  == true) {

            if(!empty($supplier->mobile_no))
            {
                $uArr = [
                    'name' => $asset->title,
                    'supplier_name' => $supplier->name,
                    'location' => $location->location_name
                ];
                SendMsg::SendMsgs($supplier->mobile_no , $uArr , 'New Asset');
            }


        }
    }
}
