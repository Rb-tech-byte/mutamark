<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Workflow\Events\CreateWorkflow;
use Workdo\Workflow\Entities\WorkflowModule;
use App\Models\User;

class CreateWorkflowLis
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
    public function handle(CreateWorkflow $event)
    {
        $Workflow = $event->Workflow;

            $module = WorkflowModule::find($Workflow->module_name);
            $user = User::where('id',$Workflow->created_by)->first();

            if(module_is_active('SMS') && !empty(company_setting('SMS New Workflow')) && company_setting('SMS New Workflow')  == true)
            {
                if(!empty($user->mobile_no))
                {
                    $uArr = [
                        'name' => $Workflow->name,
                        'module' => $module->module
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Workflow');
                }
            }

    }
}
