<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CMMS\Events\CreateComponent;

class CreateComponentLis
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
    public function handle(CreateComponent $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Component')) && company_setting('Whatsappapi New Component')  == true)
        {
            $request = $event->request;
            $component = $request->name;
            $company = User::find($event->components->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($component) && !empty($to)){
                $uArr = [
                    'component_name' => $component,
                ];
                
                SendMsg::SendMsgs($to, $uArr , 'New Component');
            }
        }
    }
}
