<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\LMS\Events\CreateCourse;
use Illuminate\Support\Facades\Auth;
class CreateCourseLis
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
    public function handle(CreateCourse $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Course')) && company_setting('Whatsappapi New Course')  == true)
        {
            $course = $event->course;
            $to = Auth::user()->mobile_no;

            if(!empty($course) && !empty($to))
            {
                $store = \Modules\LMS\Entities\Store::where('workspace_id',getActiveWorkSpace())->first();

                $uArr = [
                    'course_name' => $course->title,
                    'store_name' => $store->name,
                ];

                SendMsg::SendMsgs($to,$uArr , 'New Course');
            }
        }
    }
}
