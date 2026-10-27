<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Lead\Events\CreateDeal;

class CreateDealLis
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
    public function handle(CreateDeal $event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Deal')) && company_setting('Whatsappapi New Deal')  == true)
        {
            $request = $event->request;
            $Assign_user_phones = User::whereIn('id', $request->clients)->get();
            foreach ($Assign_user_phones as $Assign_user_phone) {
                if (!empty($Assign_user_phone->mobile_no))
                {
                    $uArr = [];
                    SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Deal');
                }
            }
        }
    }
}
