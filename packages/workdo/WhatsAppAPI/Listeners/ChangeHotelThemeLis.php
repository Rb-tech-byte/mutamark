<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Holidayz\Events\ChangeHotelTheme;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;

class ChangeHotelThemeLis
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
    public function handle(ChangeHotelTheme $event)
    {
        
        $hotel = ($event->hotel);
        
        $user = User::where('id',$hotel->created_by)->first();

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Change Hotel Theme')) && company_setting('Whatsappapi Change Hotel Theme')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'Change Hotel Theme');
            }
        }

    }
}
