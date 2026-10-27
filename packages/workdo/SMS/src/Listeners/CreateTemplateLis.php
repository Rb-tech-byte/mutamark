<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Feedback\Events\CreateTemplate;
use Workdo\Feedback\Entities\TemplateModule;
use App\Models\User;



class CreateTemplateLis
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
    public function handle(CreateTemplate $event)
    {
        $templates = $event->templates;
        $module = TemplateModule::find($templates->module);
        if (module_is_active('SMS') && !empty(company_setting('SMS New Template')) && company_setting('SMS New Template')  == true) {

            $user = \auth::user();
            if(empty($user)){
              $user = User::find($templates->created_by);
            }
            $to = $user->mobile_no;
            if(!empty($module))
            {
                $uArr = [
                    'submodule_name' => $module->submodule,
                    'module_name' => $module->module,
                ];
            }

            SendMsg::SendMsgs($to, $uArr , 'New Template');

        }
    }
}
