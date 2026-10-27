<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SalesAgent\Events\SalesAgentRequestReject;
use App\Models\User;
use Workdo\SalesAgent\Entities\Customer;

class SalesAgentRequestRejectLis
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
    public function handle(SalesAgentRequestReject $event)
    {
        $program = $event->program;

        if (module_is_active('SMS') && !empty(company_setting('SMS Sales Agent Request Reject')) && company_setting('SMS Sales Agent Request Reject')  == true) {
            if(module_is_active('Account')){
                $users = Customer::whereIn('user_id' , explode(',' , $program->sales_agents_applicable))->get();
                foreach ($users as $user_no) {
                    if (!empty($user_no->contact)) {
                        $uArr = [];
                        SendMsg::SendMsgs($user_no->contact , $uArr, 'Sales Agent Request Reject');
                    }
                }
            }else{
                $users = User::whereIn('id', explode(',', $program->sales_agents_applicable))->get();
                foreach ($users as $user_no) {
                    if (!empty($user_no->mobile_no)) {
                        $uArr = [];
                        SendMsg::SendMsgs($user_no->mobile_no , $uArr, 'Sales Agent Request Reject');
                    }
                }
            }

        }
    }
}
