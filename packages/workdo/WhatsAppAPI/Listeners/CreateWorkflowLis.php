<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Workflow\Events\CreateWorkflow;
use Modules\Workflow\Entities\WorkflowModule;

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

            if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
            && !empty(company_setting('Whatsappapi New Workflow')) && company_setting('Whatsappapi New Workflow')  == true)
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
