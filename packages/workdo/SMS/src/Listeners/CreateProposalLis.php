<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CreateProposal;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;



class CreateProposalLis
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
    public function handle(CreateProposal $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Proposal')) && company_setting('SMS New Proposal')  == true)
        {
            $request = $event->request;
            $user = \Workdo\Account\Entities\Customer::where('user_id',$request->customer_id)->first();
            if(!empty($user)){
                $user->mobile_no = $user->contact;
            }
            if(empty($user))
            {
                $user =User::where('id',$request->customer_id)->first();
            }
            if(!empty($user->mobile_no)){
                $uArr = [];
                SendMsg::SendMsgs($user->mobile_no, $uArr , 'New Proposal');
            }
        }
    }
}
