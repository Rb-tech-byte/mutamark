<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Events\StatusChangeProposal;

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
    public function handle(StatusChangeProposal $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Proposal Status Updated')) && company_setting('Whatsappapi Proposal Status Updated')  == true)
        {
            $proposal = $event->proposal;
            $user = \Modules\Account\Entities\Customer::where('user_id',$proposal->customer_id)->first();
            if(!empty($user))
            {
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
