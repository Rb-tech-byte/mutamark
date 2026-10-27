<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateComponent;
use Workdo\FixEquipment\Entities\FixAsset;
use Illuminate\Support\Facades\Auth;

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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Fix Equipment Component')) && company_setting('SMS New Fix Equipment Component')  == true) {

            $uArr = [
                'name' => $component->title,
                'assets'=> $asset->title
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to,$uArr , 'New Fix Equipment Component');

        }
    }
}
