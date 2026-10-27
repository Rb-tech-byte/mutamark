<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\LMS\Events\CreateCourseOrder;
use Workdo\LMS\Entities\Student;
class CreateCourseOrderLis
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
    public function handle(CreateCourseOrder $event)
    {

        if(module_is_active('SMS') && !empty(company_setting('SMS New Course Order')) && company_setting('SMS New Course Order')  == true)
        {
            $courseOrder = $event->courseOrder;

            if(!empty($courseOrder))
            {
                $student = Student::where('id',$courseOrder->student_id)->first();
                if(!empty($student->phone_number)){
                    $to = $student->phone_number;
                $uArr = [
                    'student_name' => $student->name,
                ];

                SendMsg::SendMsgs($to,$uArr , 'New Course Order');
                }
            }
        }
    }
}
