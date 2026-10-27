<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Taskly\Events\UpdateTaskStage;
use Illuminate\Support\Facades\Auth;


class UpdateTaskStageLis
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
    public function handle(UpdateTaskStage $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Task Stage Updated')) && company_setting('SMS Task Stage Updated')  == true)
        {
            $request = $event->request;
            $task = $event->task;
            if(!empty($task))
            {
                $new_status   = \Workdo\Taskly\Entities\Stage::where('id',$request->new_status)->first();
                $old_status   = \Workdo\Taskly\Entities\Stage::where('id',$request->old_status)->first();

                $uArr = [
                    'task_name' => $task->title,
                    'old_status' => $old_status->name,
                    'new_status' => $new_status->name,
                ];
                $to = Auth::user()->mobile_no;

                SendMsg::SendMsgs($to,$uArr , 'Task Stage Updated');
            }
        }
    }
}
