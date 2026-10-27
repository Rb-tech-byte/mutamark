<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\TimeTracker\Events\CreateTimeTracker;
use Workdo\Taskly\Entities\Project;
use Workdo\Taskly\Entities\Task;
use App\Models\User;
class CreateTimeTrackerLis
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
    public function handle(CreateTimeTracker $event)
    {
        $track = $event->track;
        $task = Task::find($track->task_id);

        $project = Project::find($track->project_id);
        $users =  User::whereIn('id' , explode(',' , $task->assign_to))->get();

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Tracker')) && company_setting('SMS New Tracker')  == true) {

            foreach($users as $user)
            {
                if(!empty($track) && !empty($project) && !empty($user->mobile_no))
                {
                    $uArr = [
                        'task_name' => $track->name,
                        'project_name' => $project->name,
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Tracker');
                }
            }

        }
    }
}
