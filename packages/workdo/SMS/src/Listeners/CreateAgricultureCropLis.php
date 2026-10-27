<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureCrop;
use Illuminate\Support\Facades\Auth;

class CreateAgricultureCropLis
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
    public function handle(CreateAgricultureCrop $event)
    {
        $agriculturecrop = $event->agriculturecrop;
        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Crop')) && company_setting('SMS New Agriculture Crop')  == true) {

            if(!empty($agriculturecrop))
            {
                $uArr = [
                    'crop_name' => $agriculturecrop->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Crop');
            }
        }
    }
}
