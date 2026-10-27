<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\LMS\Events\CreateRatting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class CreateRattingLis
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
    public function handle(CreateRatting $event)
    {
        $ratting = $event->ratting;
        if(!empty($ratting)){
            $store = \Workdo\LMS\Entities\Store::where('slug',$ratting->slug)->first();
            $student = \Workdo\LMS\Entities\Student::where('id',$ratting->student_id)->first();
            $course = \Workdo\LMS\Entities\Course::where('id',$ratting->course_id)->first();
            $user = User::find($store->created_by);

            if(module_is_active('SMS') && !empty(company_setting('SMS New Rating',$store->created_by,$store->workspace_id)) && company_setting('SMS New Rating',$store->created_by,$store->workspace_id)  == true)
            {
                if(!empty($user->mobile_no)){
                    $uArr = [
                        'student_name' => $student->name,
                        'course_name'  => $course->title,
                        'store_name'   => $store->name,
                    ];
                    $to = $user->mobile_no;
                        SendMsg::SendMsgs($to , $uArr , 'New Rating' ,$store->created_by ,$store->workspace_id);
                }
            }
        }
    }
}
