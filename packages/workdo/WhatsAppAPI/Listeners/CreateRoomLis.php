<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Holidayz\Events\CreateRoom;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;

class CreateRoomLis
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
    public function handle(CreateRoom $event)
    {
        
        $room = ($event->room);
        $user = User::find($room->created_by);

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Room')) && company_setting('Whatsappapi New Room')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name,
                    'name' => $room->room_type,
                    'price' => currency_format_with_sym($room->final_price),
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Room');
            }
        }

    }
}
