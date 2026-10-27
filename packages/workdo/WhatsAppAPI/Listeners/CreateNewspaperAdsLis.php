<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Newspaper\Events\CreateNewspaperAds;
use Modules\Newspaper\Entities\Newspaper;

class CreateNewspaperAdsLis
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
    public function handle(CreateNewspaperAds $event)
    {
        $ad = $event->ad;
        $news = Newspaper::find($ad->newspaper);
        $to =\Auth::user()->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Advertisement')) && company_setting('Whatsappapi New Advertisement')  == true) {

            if(!empty($news) && !empty($ad) && !empty($to))
            {
                $uArr = [
                    'advertidsement' => $ad->name,
                    'newspaper_name' => $news->name,
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Advertisement');
            }
        }
    }
}
