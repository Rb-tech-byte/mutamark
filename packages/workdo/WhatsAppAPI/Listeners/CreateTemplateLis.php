<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Feedback\Events\CreateTemplate;
use Modules\Feedback\Entities\TemplateModule;

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
            $to = \Auth::user()->mobile_no;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Template')) && company_setting('Whatsappapi New Template')  == true) {
            if(!empty($module) && !empty($to))
            {
                $uArr = [
                    'submodule_name' => $module->submodule,
                    'module_name' => $module->module,
                ];
            }

            SendMsg::SendMsgs($to , $uArr , 'New Template');

        }
    }
}
