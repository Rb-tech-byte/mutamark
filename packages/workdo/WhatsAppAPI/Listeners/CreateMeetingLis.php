<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Sales\Events\CreateMeeting;

class CreateMeetingLis
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
    public function handle(CreateMeeting $event)
    {

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Meeting Assigned')) && company_setting('Whatsappapi Meeting Assigned')  == true)
        {
            $request = $event->request;
            $Assign_user_phone = User::where('id',$request->user)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'meeting_name' => $request->name
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Meeting Assigned');
            }
        }
    }
}
