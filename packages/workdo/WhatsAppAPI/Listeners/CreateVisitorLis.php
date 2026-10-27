<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VisitorManagement\Events\CreateVisitor;

class CreateVisitorLis
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
    public function handle(CreateVisitor $event)
    {
        $visitor = $event->visitor;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Visitor')) && company_setting('Whatsappapi New Visitor')  == true) {

            if(!empty($visitor->phone))
            {
                $uArr = [
                    'name' => $visitor->first_name . $visitor->last_name,
                ];
    
                SendMsg::SendMsgs($visitor->phone , $uArr , 'New Visitor');
            }


        }
    }
}
