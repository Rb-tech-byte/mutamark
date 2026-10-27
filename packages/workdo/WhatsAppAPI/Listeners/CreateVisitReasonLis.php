<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VisitorManagement\Events\CreateVisitReason;
use App\Models\User;

class CreateVisitReasonLis
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
    public function handle(CreateVisitReason $event)
    {
        $visitorReason = $event->visitorReason;

        $user = User::find($visitorReason->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Visit Reason')) && company_setting('Whatsappapi New Visit Reason')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $visitorReason->reason
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Visit Reason');
            }

        }
    }
}
