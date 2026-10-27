<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Lead\Events\LeadMoved;

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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Lead Moved')) && company_setting('Whatsappapi Lead Moved')  == true)
        {
            $lead = $event->lead;
            $request = $event->request;
            $newStage = \Modules\Lead\Entities\LeadStage::where('id',$request->stage_id)->first();
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
