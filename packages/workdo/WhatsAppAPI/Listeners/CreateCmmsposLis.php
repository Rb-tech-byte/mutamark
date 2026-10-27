<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CMMS\Events\CreateCmmspos;

class CreateCmmsposLis
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
    public function handle(CreateCmmspos $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New POs')) && company_setting('Whatsappapi New POs')  == true)
        {
            $request = $event->request;
            $user = User::find($request->user_id);
            $company = User::find($event->Pos->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($user) && !empty($to)){
                $uArr = [
                    'user_name' => $user->name,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New POs');
            }
        }
    }
}
