<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Rotas\Events\CreateAvailability;

class CreateAvailabilityLis
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
    public function handle(CreateAvailability $event)
    {
        $availability = $event->availability;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Availabilitys')) && company_setting('Whatsappapi New Availabilitys')  == true)
         {
            $Assign_user_phone = \Modules\Hrm\Entities\Employee::where('id', $availability->employee_id)->first();
            if (!empty($Assign_user_phone->phone)) {
                $uArr = [];
                SendMsg::SendMsgs($Assign_user_phone->phone, $uArr , 'New Availabilitys');
            }
        }
    }
}
