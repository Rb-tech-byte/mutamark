<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CMMS\Entities\Part;
use Workdo\CMMS\Events\CreatePms;
class CreatePmsLis
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
    public function handle(CreatePms $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Pms')) && company_setting('SMS New Pms')  == true)
        {
            $request = $event->request;
            $parts_item = Part::whereIn('id',$request->parts)->get();
            $to=\Auth::user()->mobile_no;
            $part = [];
            foreach ($parts_item as $item) {
                $part[] = $item['name'];
            }
            $parts = implode(',', $part);

            $company = User::find($event->pms->company_id);
            if(!empty($parts) && !empty($to)){

                $uArr = [
                    'part_name' => $parts,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Pms');
            }
        }
    }
}
