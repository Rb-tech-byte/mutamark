<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\School\Events\CreateAdmission;

class CreateAdmissionLis
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
    public function handle(CreateAdmission $event)
    {
        $admission = $event->admission;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Addmissions')) && company_setting('Whatsappapi New Addmissions')  == true) {

            if(!empty($admission) && !empty($admission->phone))
            {
                $uArr = [
                    'student_name' => $admission->student_name
                ];
                SendMsg::SendMsgs($admission->phone , $uArr , 'New Addmissions');
            }
        }
    }
}
