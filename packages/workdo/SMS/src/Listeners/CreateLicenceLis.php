<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateLicence;
use Workdo\FixEquipment\Entities\FixAsset;
use Illuminate\Support\Facades\Auth;

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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Licence')) && company_setting('SMS New Licence')  == true) {

            $uArr = [
                'name' => $license->title,
                'assets' => $asset->title
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $uArr , 'New Licence');

        }
    }
}
