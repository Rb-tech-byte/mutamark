<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Assets\Events\CreateAssetExtra;
use Workdo\Assets\Entities\Asset;
use App\Models\User;

class CreateAssetExtraLis
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
    public function handle(CreateAssetExtra $event)
    {
        $assetextra = $event->assetextra;
        $asset = Asset::find($assetextra->asset_id);
        $user = User::find($asset->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Asset Extra')) && company_setting('SMS New Asset Extra')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'asset' => !empty($asset) ? $asset->name : '-'
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Asset Extra');
            }

        }
    }
}
