<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateAsset;
use Workdo\FixEquipment\Entities\EquipmentLocation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        if (module_is_active('SMS') && !empty(company_setting('SMS New Asset')) && company_setting('SMS New Asset')  == true) {

            $uArr = [
                'name' => $asset->title,
                'supplier_name' => $supplier->name,
                'location' => $location->location_name
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $uArr , 'New Asset');

        }
    }
}
