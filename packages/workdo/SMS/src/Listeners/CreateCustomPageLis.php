<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\LMS\Events\CreateCustomPage;
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
        if(module_is_active('SMS') && !empty(company_setting('SMS New Custom Page')) && company_setting('SMS New Custom Page')  == true)
        {
            $pageOption = $event->pageOption;
            if(!empty($pageOption))
            {
                $store = \Workdo\LMS\Entities\Store::where('workspace_id',getActiveWorkSpace())->first();

                $uArr = [
                    'page_name' => $pageOption->name,
                    'store_name' => $store->name
                ];
                $to = Auth::user()->mobile_no;

                SendMsg::SendMsgs($to,$uArr , 'New Custom Page');
            }
        }
    }
}
