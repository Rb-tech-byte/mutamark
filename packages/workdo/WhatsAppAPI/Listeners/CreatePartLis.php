<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CMMS\Events\CreatePart;

class CreatePartLis
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
    public function handle(CreatePart $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Part')) && company_setting('Whatsappapi New Part')  == true)
        {
            $request = $event->request;
            $part = $request->name;
            $company = User::find($event->parts->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($part) && !empty($to)){
                $uArr = [
                    'part_name'=>$part,
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Part');
            }
        }
    }
}
