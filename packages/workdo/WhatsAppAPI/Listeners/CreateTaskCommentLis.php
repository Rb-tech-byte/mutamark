<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Taskly\Events\CreateTaskComment;
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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Task Comment')) && company_setting('Whatsappapi New Task Comment')  == true)
        {
            $comment = $event->comment;
            $to = Auth::user()->mobile_no;
            if(!empty($comment) && !empty($to))
            {
                $task = \Modules\Taskly\Entities\Task::where('id',$comment->task_id)->first();
                $uArr = [
                    'task_name' => $task->title,
                ];

                SendMsg::SendMsgs($to,$uArr , 'New Task Comment');
            }
        }
    }
}
