<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Lead\Events\CreateLead;
use App\Models\User;


class CreateLeadLis
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
    public function handle(CreateLead $event)
    {
        $request = $event->lead;
        if (module_is_active('SMS') && !empty(company_setting('SMS New Lead')) && company_setting('SMS New Lead')  == true) {
            $Assign_user_phone = User::where('id', $request->user_id)->first();
            if ($Assign_user_phone->mobile_no) {

                $uArr = [];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Lead');
            }
        }
    }
}
