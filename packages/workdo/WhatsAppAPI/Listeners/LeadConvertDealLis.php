<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Lead\Events\LeadConvertDeal;

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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Lead to Deal Conversion')) && company_setting('Whatsappapi Lead to Deal Conversion')  == true)
        {
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
