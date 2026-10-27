<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Assets\Events\CreateAssets;



class CreateAssetsLis
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
    public function handle(CreateAssets $event)
    {
        $assets = $event->assets;

        $to = \Auth::user()->mobile_no;
        if (module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Asset')) && company_setting('SMS New Asset') == true) {

            if (!empty($to)) {
                $uArr = [
                    'name' => $assets->name,
                ];

                SendMsg::SendMsgs($to , $uArr, 'New Asset');
            }
        }
    }
}
