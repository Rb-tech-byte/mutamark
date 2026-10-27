<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Events\CreateUser;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;

class UserCreate
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
    public function handle(CreateUser $event)
    {
        $request    = $event->request;
        if(company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Create User')) && company_setting('Whatsappapi Create User') == true)
        {
            $user       = User::find($event->user->id);
            if($user){
                $user->mobile_no = $event->request->has('mobile_no') ? $event->request->mobile_no:'';
                $user->save();
    
                if(module_is_active('WhatsAppAPI'))
                {
                    if(!empty($request['mobile_no'])){
                        $uArr = [
                            'user_name' => $user->name,
                        ];
                        SendMsg::SendMsgs($request['mobile_no'] , $uArr , 'Create User');
                    }
                }
            }
        }
    }
}
