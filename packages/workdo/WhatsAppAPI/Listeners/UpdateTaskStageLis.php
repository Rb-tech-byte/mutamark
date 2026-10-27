<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Taskly\Events\UpdateTaskStage;
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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Task Stage Updated')) && company_setting('Whatsappapi Task Stage Updated')  == true)
        {
            $request = $event->request;
            $task = $event->task;
            $to = Auth::user()->mobile_no;
            if(!empty($task) && !empty($to))
            {
                $new_status   = \Modules\Taskly\Entities\Stage::where('id',$request->new_status)->first();
                $old_status   = \Modules\Taskly\Entities\Stage::where('id',$request->old_status)->first();

                $uArr = [
                    'task_name' => $task->title,
                    'old_status' => $old_status->name,
                    'new_status' => $new_status->name,
                ];

                SendMsg::SendMsgs($to,$uArr , 'Task Stage Updated');
            }
        }
    }
}
