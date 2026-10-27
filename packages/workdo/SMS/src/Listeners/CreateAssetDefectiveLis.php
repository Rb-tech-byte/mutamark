<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Assets\Events\CreateAssetDefective;
use Workdo\Assets\Entities\Asset;
use App\Models\User;


class CreateAssetDefectiveLis
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
    public function handle(CreateAssetDefective $event)
    {
        $assetdefective = $event->assetdefective;
        $asset = Asset::find($assetdefective->asset_id);
        $user = User::find($asset->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Asset Defective')) && company_setting('SMS New Asset Defective')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'asset' => !empty($asset) ? $asset->name : ''
                ];

                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Asset Defective');
            }

        }
    }
}
