<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;
use Modules\CMMS\Events\CreateSupplier;

class CreateSupplierLis
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
    public function handle(CreateSupplier $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Supplier')) && company_setting('Whatsappapi New Supplier')  == true)
        {
            $request = $event->request;
            $user = $request->name;
            $company = User::find($event->suppliers->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($user) && !empty($to)){
                $uArr = [
                    'user_name' =>$user,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Supplier');
            }
        }
    }
}
