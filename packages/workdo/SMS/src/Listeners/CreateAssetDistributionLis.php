<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Assets\Events\CreateAssetDistribution;
use App\Models\User;

class CreateAssetDistributionLis
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
    public function handle(CreateAssetDistribution $event)
    {
        $asset = $event->asset;
        $user = User::find($asset->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Asset Distribution')) && company_setting('SMS New Asset Distribution')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'asset' => $asset->name
                ];

                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Asset Distribution');
            }

        }
    }
}
