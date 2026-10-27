<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\School\Entities\Classroom;
use Workdo\School\Entities\SchoolStudent;
use Workdo\School\Events\CreateSubject;
use Workdo\SMS\Entities\SendMsg;

class CreateSubjectLis
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
    public function handle(CreateSubject $event)
    {
        $subject = $event->subject;
        $class = Classroom::find($subject->class_id);
        $students = SchoolStudent::where('class_name', $class->id)->get();

        if (module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Subject')) && company_setting('SMS New Subject') == true) {
            foreach ($students as $student) {
                if(!empty($student->contact))
                {
                    $uArr = [
                        'subject_name' => $subject->subject_name,
                        'class_name' => $class->class_name
                    ];
                    SendMsg::SendMsgs($student->contact , $uArr , 'New Subject');
                }
            }
        }
    }
}
