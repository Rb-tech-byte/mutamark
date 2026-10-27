<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateConsumables;
use Modules\FixEquipment\Entities\FixAsset;
use App\Models\User;

class CreateConsumablesLis
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
    public function handle(CreateConsumables $event)
    {
        $consumables = $event->consumables;
        $asset = FixAsset::find($consumables->asset);

        $user = User::find($consumables->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Consumables')) && company_setting('Whatsappapi New Consumables')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $consumables->title,
                    'assets' => $asset->title
                ];
                SendMsg::SendMsgs($user->mobile_no ,  $uArr , 'New Consumables');
            }


        }
    }
}
