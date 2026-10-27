<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ChildcareManagement\Events\CreateParent;

class CreateParentLis
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
    public function handle(CreateParent $event)
    {
        $parent = $event->parent;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Parent')) && company_setting('Whatsappapi New Parent')  == true) {

            if(!empty($parent->contact_number))
            {
                $uArr = [
                    'parent_name' => $parent->first_name . ' '.$parent->last_name
                ];
                SendMsg::SendMsgs($parent->contact_number, $uArr , 'New Parent');
            }
        }
    }
}
