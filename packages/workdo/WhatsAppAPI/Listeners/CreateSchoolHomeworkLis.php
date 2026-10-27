<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\School\Events\CreateSchoolHomework;
use Modules\School\Entities\Classroom;
use Modules\School\Entities\Subject;
use Modules\School\Entities\SchoolStudent;

class CreateSchoolHomeworkLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CreateSchoolHomework $event)
    {
        $homework = $event->homework;
        $class = Classroom::find($homework->classroom);
        $subject = Subject::find($homework->subject);

        $students = SchoolStudent::where('class_name' , $class->id)->get();        

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Homework')) && company_setting('Whatsappapi New Homework')  == true) {

            foreach($students as $student)
            {
                if(!empty($homework) && !empty($class) && !empty($subject) && !empty($student->contact))
                {
                    $uArr = [
                        'homework_title' => $homework->title,
                        'class_name' => $class->class_name,
                        'subject' => $subject->subject_name
                    ];
                    SendMsg::SendMsgs($student->contact, $uArr , 'New Homework');
        
                }
            }


        }
    }
}
