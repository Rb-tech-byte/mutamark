<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Newspaper\Events\CreateNewspaperAds;
use Workdo\Newspaper\Entities\Newspaper;

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

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Advertisement')) && company_setting('SMS New Advertisement')  == true) {
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
