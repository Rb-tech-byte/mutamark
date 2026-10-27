<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\LMS\Events\CreateRatting;
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
            $store = \Modules\LMS\Entities\Store::where('slug',$ratting->slug)->first();
            $student = \Modules\LMS\Entities\Student::where('id',$ratting->student_id)->first();
            $course = \Modules\LMS\Entities\Course::where('id',$ratting->course_id)->first();
            $user = User::find($store->created_by);
            
            if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is' , $store->created_by , $store->workspace_id)=='on' 
            && !empty(company_setting('Whatsappapi New Rating',$store->created_by,$store->workspace_id)) && company_setting('Whatsappapi New Rating',$store->created_by,$store->workspace_id)  == true)
            {
                if(!empty($user->mobile_no)){
                    $uArr = [
                        'student_name' => $student->name,
                        'course_name'  => $course->title,
                        'store_name'   => $store->name,
                    ];
                        SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Rating' ,$store->created_by ,$store->workspace_id);
                }
            }
        }
    }
}
