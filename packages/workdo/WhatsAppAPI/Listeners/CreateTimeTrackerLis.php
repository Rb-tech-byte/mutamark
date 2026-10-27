<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\TimeTracker\Events\CreateTimeTracker;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\Task;
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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Tracker')) && company_setting('Whatsappapi New Tracker')  == true) {

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
