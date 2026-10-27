<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\CMMS\Events\CreateSupplier;
class CreateSupplierLis
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
    public function handle(CreateSupplier $event)
    {
        if(module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Supplier')) && company_setting('SMS New Supplier')  == true)
        {
            $request = $event->request;
            $user = $request->name;
            $company = User::find($event->suppliers->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($user) && !empty($to)){
                $uArr = [
                    'user_name' =>$user,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Supplier');
            }
        }
    }
}
