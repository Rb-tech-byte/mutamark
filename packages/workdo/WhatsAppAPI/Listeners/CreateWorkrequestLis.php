<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CMMS\Entities\Component;
use Modules\CMMS\Events\CreateWorkrequest;

class CreateWorkrequestLis
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
    public function handle(CreateWorkrequest $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is', $event->workorder->company_id)=='on' && !empty(company_setting('Whatsappapi Work Order Request' , $event->workorder->company_id)) && company_setting('Whatsappapi Work Order Request' , $event->workorder->company_id)  == true)
        {
            $request = $event->request;
            $user = $request->user_name;
            $component = Component::find($request->components_id);
            $user_no = User::where('id',$event->workorder->company_id)->first();
            $to=!empty(\Auth::user()->mobile_no) ? \Auth::user()->mobile_no : $user_no->mobile_no;
            if(!empty($component)){
                $uArr = [
                    'component_name' => $component->name,
                    'user_name' => $user,
                ];
                SendMsg::SendMsgs($to, $uArr , 'Work Order Request',$component->company_id ,$component->workspace);
            }
        }
    }
}
