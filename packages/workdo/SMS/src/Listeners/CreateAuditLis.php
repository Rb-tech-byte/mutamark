<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateAudit;
use Workdo\FixEquipment\Entities\FixAsset;
use Illuminate\Support\Facades\Auth;

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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Audit')) && company_setting('SMS New Audit')  == true) {

            $uArr = [
                'name' => $audit->audit_title,
                'assets' => $assets
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to ,$uArr , 'New Audit');

        }
    }
}
