<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CMMS\Events\CreatePart;

class CreatePartLis
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
    public function handle(CreatePart $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Part')) && company_setting('SMS New Part')  == true)
        {
            $request = $event->request;
            $part = $request->name;
            $company = User::find($event->parts->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($part) && !empty($to)){
                $uArr = [
                    'part_name'=>$part,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Part');
            }
        }
    }
}
