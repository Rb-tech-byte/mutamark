<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Taskly\Events\CreateBug;

class CreateBugLis
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
    public function handle(CreateBug $event)
    {
        $request = $event->bug;

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Bug')) && company_setting('Whatsappapi New Bug')  == true)
        {
            $Assign_user_phone = User::where('id',$request->assign_to)->first();
            $project = \Modules\Taskly\Entities\Project::where('id',$request->project_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'bug_name' => $request->title,
                    'project_name'=>$project->name,                    
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Bug');
            }
        }
    }
}
