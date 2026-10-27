<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Taskly\Events\CreateBug;
use App\Models\User;



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

        if(module_is_active('SMS') && !empty(company_setting('SMS New Bug')) && company_setting('SMS New Bug')  == true)
        {
            $Assign_user_phone = User::where('id',$request->assign_to)->first();
            $project = \Workdo\Taskly\Entities\Project::where('id',$request->project_id)->first();
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
