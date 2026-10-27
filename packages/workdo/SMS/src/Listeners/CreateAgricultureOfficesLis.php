<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureOffices;
use Workdo\AgricultureManagement\Entities\AgricultureDepartment;
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

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Office')) && company_setting('SMS New Agriculture Office')  == true) {

            if(!empty($agricultureoffice))
            {
                $department = AgricultureDepartment::where('id' , $agricultureoffice->department)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'office_name' => $agricultureoffice->name,
                    'department'  => $department->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Office');
            }
        }
    }
}
