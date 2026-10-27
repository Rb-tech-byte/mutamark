<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateLicence;
use Modules\FixEquipment\Entities\FixAsset;
use App\Models\User;

class CreateLicenceLis
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
    public function handle(CreateLicence $event)
    {
        $license = $event->license;
        $asset = FixAsset::find($license->asset);

        $user = User::find($license->created_by);

        if (module_is_active('WhatsAppAPI')&& company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Licence')) && company_setting('Whatsappapi New Licence')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $license->title,
                    'assets' => $asset->title
                ];
                SendMsg::SendMsgs($user->mobile_no ,  $uArr , 'New Licence');
            }

        }
    }
}
