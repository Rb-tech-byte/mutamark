<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Recruitment\Events\ConvertToEmployee;

class ConvertToEmployeeLis
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
    public function handle(ConvertToEmployee $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Convert To Employee')) && company_setting('SMS Convert To Employee')  == true)
        {
            $request = $event->request;
            if(!empty($request['phone'])){
                $uArr = [];
                SendMsg::SendMsgs($request['phone'], $uArr , 'Convert to Employee');
            }
        }
    }
}
