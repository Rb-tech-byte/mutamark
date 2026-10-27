<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\LMS\Events\CreateCourse;
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
        if(module_is_active('SMS') && !empty(company_setting('SMS New Course')) && company_setting('SMS New Course')  == true)
        {
            $course = $event->course;
            if(!empty($course))
            {
                $store = \Workdo\LMS\Entities\Store::where('workspace_id',getActiveWorkSpace())->first();

                $uArr = [
                    'course_name' => $course->title,
                    'store_name' => $store->name,
                ];
                $to = Auth::user()->mobile_no;

                SendMsg::SendMsgs($to,$uArr , 'New Course');
            }
        }
    }
}
