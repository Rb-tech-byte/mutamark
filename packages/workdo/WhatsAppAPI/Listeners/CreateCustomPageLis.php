<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\LMS\Events\CreateCustomPage;
use Illuminate\Support\Facades\Auth;

class CreateCustomPageLis
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
    public function handle(CreateCustomPage $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Custom Page')) && company_setting('Whatsappapi New Custom Page')  == true)
        {
            $pageOption = $event->pageOption;
            $to = Auth::user()->mobile_no;

            if(!empty($pageOption) && !empty($to))
            {
                $store = \Modules\LMS\Entities\Store::where('workspace_id',getActiveWorkSpace())->first();

                $uArr = [
                    'page_name' => $pageOption->name,
                    'store_name' => $store->name
                ];

                SendMsg::SendMsgs($to,$uArr , 'New Custom Page');
            }
        }
    }
}
