<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CMMS\Events\CreateWorkorder;


class CreateWorkorderLis
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
    public function handle(CreateWorkorder $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS Work Order Assigned')) && company_setting('SMS Work Order Assigned')  == true)
        {
            $request = $event->request;
            $wo_name = $request->wo_name;
            $user = User::whereIn('id', explode(',', $event->workorder->sand_to))->get()->pluck('name');
            $user_detail = [];
                if (count($user) > 0) {
                    foreach ($user as $datasand) {
                        $user_detail[] = $datasand;
                    }
                }
            $user = implode(',', $user_detail);

            $company = User::find($event->workorder->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($user) && !empty($to)){
                $uArr = [
                    'wo_name' => $wo_name,
                    'user_name' => $user,
                ];
                SendMsg::SendMsgs($to, $uArr , 'Work Order Assigned');
            }
        }
    }
}
