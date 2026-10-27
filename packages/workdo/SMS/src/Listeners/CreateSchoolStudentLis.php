<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\School\Events\CreateSchoolStudent;
use Workdo\School\Entities\Classroom;
class CreateSchoolStudentLis
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
    public function handle(CreateSchoolStudent $event)
    {
        $student = $event->student;
        $class = Classroom::find($student->class_name);

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Students')) && company_setting('SMS New Students')  == true) {

            if(!empty($student) && !empty($class) && !empty($student->contact))
            {
                $uArr = [
                    'student_name' => $student->name,
                    'class_name' => $class->class_name
                ];
                SendMsg::SendMsgs($student->contact , $uArr , 'New Students');
            }
        }
    }
}
