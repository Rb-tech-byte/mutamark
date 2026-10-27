<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateComponent;
use Modules\FixEquipment\Entities\FixAsset;
use App\Models\User;

class CreateFixEquipComponentLis
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
    public function handle(CreateComponent $event)
    {
        $component = $event->component;
        $asset = FixAsset::find($component->asset);

        $user = User::find($component->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Component')) && company_setting('Whatsappapi New Component')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $component->title,
                    'assets'=> $asset->title
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Component');
            }
        }
    }
}
