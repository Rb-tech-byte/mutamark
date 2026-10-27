<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\AgricultureManagement\Events\CreateAgricultureOffices;
use Modules\AgricultureManagement\Entities\AgricultureDepartment;
use Illuminate\Support\Facades\Auth;

class CreateAgricultureOfficesLis
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
    public function handle(CreateAgricultureOffices $event)
    {
        $agricultureoffice = $event->agricultureoffice;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Agriculture Office')) && company_setting('Whatsappapi New Agriculture Office')  == true) {

            $to = Auth::user()->mobile_no;
            if(!empty($agricultureoffice) && !empty($to))
            {
                $department = AgricultureDepartment::where('id' , $agricultureoffice->department)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'office_name' => $agricultureoffice->name,
                    'department'  => $department->name,
                ];
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Office');
            }
        }
    }
}
