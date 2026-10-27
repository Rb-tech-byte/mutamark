<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Lead\Events\CreateLead;

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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Lead')) && company_setting('Whatsappapi New Lead')  == true)
        {
            $Assign_user_phone = User::where('id', $request->user_id)->first();
            if ($Assign_user_phone->mobile_no) {
                $uArr = [];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Lead');
            }
        }
    }
}
