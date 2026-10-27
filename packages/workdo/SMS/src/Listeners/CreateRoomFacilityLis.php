<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Holidayz\Events\CreateRoomFacility;
use App\Models\User;
class CreateRoomFacilityLis
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
    public function handle(CreateRoomFacility $event)
    {
        $facility = ($event->facility);
        $user = User::find($facility->created_by);

        if(module_is_active('SMS') && !empty(company_setting('SMS New Room Facility')) && company_setting('SMS New Room Facility')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name,
                    'name' => $facility->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Room Facility');
            }
        }
    }
}
