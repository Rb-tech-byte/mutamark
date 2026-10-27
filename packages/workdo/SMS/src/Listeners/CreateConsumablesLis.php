<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateConsumables;
use Workdo\FixEquipment\Entities\FixAsset;
use Illuminate\Support\Facades\Auth;

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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Consumables')) && company_setting('SMS New Consumables')  == true) {

            $uArr = [
                'name' => $consumables->title,
                'assets' => $asset->title
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to,$uArr , 'New Consumables');

        }
    }
}
