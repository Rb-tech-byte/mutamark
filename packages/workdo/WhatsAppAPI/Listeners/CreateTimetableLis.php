<?php

namespace Modules\WhatsAppAPI\Listeners;

use Modules\School\Entities\Classroom;
use Modules\School\Entities\SchoolStudent;
use Modules\School\Events\CreateTimetable;
use Modules\WhatsAppAPI\Entities\SendMsg;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Time Table')) && company_setting('Whatsappapi New Time Table') == true) {

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
