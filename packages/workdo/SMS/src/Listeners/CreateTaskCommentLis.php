<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Taskly\Events\CreateTaskComment;
use Illuminate\Support\Facades\Auth;

class CreateTaskCommentLis
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
    public function handle(CreateTaskComment $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Task Comment')) && company_setting('SMS New Task Comment')  == true)
        {
            $comment = $event->comment;
            if(!empty($comment))
            {
                $task = \Workdo\Taskly\Entities\Task::where('id',$comment->task_id)->first();
                $uArr = [
                    'task_name' => $task->title,
                ];
                $to = Auth::user()->mobile_no;

                SendMsg::SendMsgs($to,$uArr , 'New Task Comment');
            }
        }
    }
}
