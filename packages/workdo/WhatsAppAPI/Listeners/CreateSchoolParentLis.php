<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\School\Events\CreateSchoolParent;

class CreateSchoolParentLis
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
    public function handle(CreateSchoolParent $event)
    {
        $parent = $event->parent;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Parents')) && company_setting('Whatsappapi New Parents')  == true) {

            if(!empty($parent) && !empty($parent->contact))
            {
                $uArr = [
                    'parent_name' => $parent->name
                ];
                SendMsg::SendMsgs($parent->contact, $uArr , 'New Parents');
            }


        }
    }
}
