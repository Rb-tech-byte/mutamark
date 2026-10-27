<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Newspaper\Events\CreateNewspaperDistributions;

class CreateNewspaperDistributionsLis
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
    public function handle(CreateNewspaperDistributions $event)
    {
        $distribution = $event->distribution;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Newspaper Distribution Center')) && company_setting('Whatsappapi New Newspaper Distribution Center')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'name' => $distribution->name
                ];
                SendMsg::SendMsgs($to ,$uArr , 'New Newspaper Distribution Center');
            }


        }
    }
}
