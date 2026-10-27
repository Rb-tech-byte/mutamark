<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Training\Events\CreateTrainer;

class CreateTrainerLis
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
    public function handle(CreateTrainer $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Trainer')) && company_setting('Whatsappapi New Trainer')  == true)
        {
            $request = $event->request;
            $trainer = $event->trainer;
            if(!empty($request->contact)){
                $uArr = [
                    'branch_name' => $trainer->branches->name
                ];
                SendMsg::SendMsgs($request->contact, $uArr , 'New Trainer');
            }
        }
    }
}
