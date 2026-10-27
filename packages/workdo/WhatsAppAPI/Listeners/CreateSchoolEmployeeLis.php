<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\School\Events\CreateSchoolEmployee;

class CreateSchoolEmployeeLis
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
    public function handle(CreateSchoolEmployee $event)
    {
        $employee = $event->employee;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Teacher')) && company_setting('Whatsappapi New Teacher')  == true) {

            if(!empty($employee) && !empty($employee->phone))
            {
                $uArr = [
                    'teacher_name' => $employee->name
                ];
                SendMsg::SendMsgs($employee->phone , $uArr , 'New Teacher');
            }
        }
    }
}
