<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use App\Models\User;





class StatusChangeProposalLis
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
    public function handle($event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Proposal Status Updated')) && company_setting('SMS Proposal Status Updated')  == true)
        {

            $proposal = $event->proposal;
            $user = \Workdo\Account\Entities\Customer::where('user_id',$proposal->customer_id)->first();

            if(!empty($user)){
                    $user->mobile_no = $user->contact;
            }

            if(empty($user))
            {
                $user =User::where('id',$proposal->customer_id)->first();
            }

            if(!empty($user->mobile_no))
            {
                $uArr = [];
                SendMsg::SendMsgs($user->mobile_no , $uArr,'Proposal Status Updated');
            }
        }
    }
}
