<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Events\CreateCompanyPolicy;
class CreateCompanyPolicyLis
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
    public function handle(CreateCompanyPolicy $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Company Policy')) && company_setting('SMS New Company Policy')  == true)
        {
            $request = $event->request;
            $to= Auth::user()->mobile_no;
            $policy = $event->policy;

            $branch = \Workdo\Hrm\Entities\Branch::find($request->branch);
            if(!empty($to)){
                $uArr = [
                    'name' => $request->title,
                    'branch_name' => $policy->branches->name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Company Policy');
            }
        }
    }
}
