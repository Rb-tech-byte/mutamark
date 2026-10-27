<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Holidayz\Events\ChangeHotelTheme;
use Workdo\SMS\Entities\SendMsg;
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

        if(module_is_active('SMS') && !empty(company_setting('SMS Change Hotel Theme')) && company_setting('SMS Change Hotel Theme')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'Change Hotel Theme');
            }
        }

    }
}
