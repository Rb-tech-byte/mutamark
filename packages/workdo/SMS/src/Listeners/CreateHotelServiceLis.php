<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Holidayz\Events\CreateHotelService;
use Workdo\SMS\Entities\SendMsg;
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

        if(module_is_active('SMS') && !empty(company_setting('SMS New Hotel Service')) && company_setting('SMS New Hotel Service')  == true)
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
