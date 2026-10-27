<?php

namespace Modules\WhatsAppAPI\Listeners;

use Modules\School\Entities\Classroom;
use Modules\School\Entities\SchoolStudent;
use Modules\School\Events\CreateSubject;
use Modules\WhatsAppAPI\Entities\SendMsg;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Subject')) && company_setting('Whatsappapi New Subject') == true) {
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
