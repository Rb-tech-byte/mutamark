<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Taskly\Events\CreateTask;

class CreateTaskLis
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
    public function handle(CreateTask $event)
    {
        $request = $event->task;
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Task')) && company_setting('Whatsappapi New Task')  == true)
        {
            $Assign_user_phones = User::whereIN('id',explode(',',$request->assign_to))->get();
            $project = \Modules\Taskly\Entities\Project::where('id',$request->project_id)->first();
            foreach($Assign_user_phones as $Assign_user_phone){
                if(!empty($Assign_user_phone->mobile_no))
                {
                    $uArr = [
                        'task_name' => $request->title,
                        'project_name' => $project->name
                    ];
                    SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Task');
                }
            }
        }
    }
}
