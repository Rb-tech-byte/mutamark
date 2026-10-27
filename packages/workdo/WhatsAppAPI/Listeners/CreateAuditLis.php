<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateAudit;
use Modules\FixEquipment\Entities\FixAsset;
use App\Models\User;

class CreateAuditLis
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
    public function handle(CreateAudit $event)
    {
        $audit = $event->audit;
        $asset = FixAsset::whereIn('id', explode(',', $audit->asset))->get()->pluck('title');
        $asset_detail = [];
            if (count($asset) > 0) {
                foreach ($asset as $datasand) {
                    $asset_detail[] = $datasand;
                }
            }
        $assets = implode(',', $asset_detail);

        $user = User::find($audit->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Audit')) && company_setting('Whatsappapi New Audit')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $audit->audit_title,
                    'assets' => $assets
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Audit');
            }


        }
    }
}
