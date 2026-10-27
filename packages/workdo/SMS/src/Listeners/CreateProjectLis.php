<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Taskly\Events\CreateProject;
use App\Models\User;


class CreateProjectLis
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
    public function handle(CreateProject $event)
    {
        $project = $event->project;
        if(module_is_active('SMS') && !empty(company_setting('SMS New Project')) && company_setting('SMS New Project')  == true)
        {
            $projects = \Workdo\Taskly\Entities\UserProject::where('project_id',$project->id)->get()->pluck('user_id');
            $Assign_user_phones  = User::whereIn('id',$projects)->get();
            foreach($Assign_user_phones as $Assign_user_phone){
                if(!empty($Assign_user_phone->mobile_no))
                {
                    $uArr = [
                        'project_name' => $project->name
                    ];
                    SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Project');
                }
            }
        }
    }
}
