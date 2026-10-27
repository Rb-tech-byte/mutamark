<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureCrop;
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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Crop')) && company_setting('Whatsappapi New Agriculture Crop')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agriculturecrop) && !empty($to))
            {
                $uArr = [
                    'crop_name' => $agriculturecrop->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Crop');
            }
        }
    }
}
