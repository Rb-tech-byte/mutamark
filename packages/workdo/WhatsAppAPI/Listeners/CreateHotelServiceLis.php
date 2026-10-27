<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Holidayz\Events\CreateHotelService;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;

class CreateHotelServiceLis
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
    public function handle(CreateHotelService $event)
    {
        
        $service = ($event->service);
        
        $user = User::find($service->created_by);

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Hotel Service')) && company_setting('Whatsappapi New Hotel Service')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name,
                    'name' => $service->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Hotel Service');
            }
        }

    }
}
