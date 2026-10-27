<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Lead\Events\LeadMoved;


class LeadMovedLis
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
    public function handle(LeadMoved $event)
    {
            if (module_is_active('SMS') && !empty(company_setting('SMS Lead Moved')) && company_setting('SMS Lead Moved')  == true)
        {
            $lead = $event->lead;
            $request = $event->request;
            $newStage = \Workdo\Lead\Entities\LeadStage::where('id',$request->stage_id)->first();
            $Assign_user_phone = User::where('id', $lead->user_id)->first();
            if (!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'lead_name' => $lead->name,
                    'old_stage' => $lead->stage->name,
                    'new_stage' => $newStage->name
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Lead Moved');
            }
        }
    }
}
