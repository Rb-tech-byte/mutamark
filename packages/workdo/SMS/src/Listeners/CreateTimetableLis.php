<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\School\Entities\Classroom;
use Workdo\School\Entities\SchoolStudent;
use Workdo\School\Events\CreateTimetable;
use Workdo\SMS\Entities\SendMsg;
class CreateTimetableLis
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
    public function handle(CreateTimetable $event)
    {
        $timetable = $event->timetable;
        $class = Classroom::find($timetable->class_id);
        $students = SchoolStudent::where('class_name', $class->id)->get();

        if (module_is_active('SMS') && company_setting('sms_notification_is') == 'on' && !empty(company_setting('SMS New Time Table')) && company_setting('SMS New Time Table') == true) {

            foreach ($students as $student) {
                if (!empty($class) && !empty($student->contact)) {
                    $uArr = [
                        'class_name' => $class->class_name,
                    ];
                    SendMsg::SendMsgs($student->contact, $uArr, 'New Time Table');
                }
            }

        }
    }
}
