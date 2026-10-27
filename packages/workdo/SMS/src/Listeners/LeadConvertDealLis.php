<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Lead\Events\LeadConvertDeal;
use App\Models\User;


class LeadConvertDealLis
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
    public function handle(LeadConvertDeal $event)
    {
        if (module_is_active('SMS') && !empty(company_setting('SMS Lead to Deal Conversion')) && company_setting('SMS Lead to Deal Conversion')  == true) {
            $lead = $event->lead;
            $Assign_user_phone = User::where('id', $lead->user_id)->first();
            if ($Assign_user_phone->mobile_no)
            {
                $uArr = [
                    'name' => $lead->name
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Lead to Deal Conversion');
            }
        }
    }
}
