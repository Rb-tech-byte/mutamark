<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Rotas\Events\AddDayoff;
class AddDayoffLis
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
    public function handle(AddDayoff $event)
    {
        $profile = $event->profile;
        $add_dayoff = $event->request;
        $date = $add_dayoff->date;

        if (module_is_active('SMS') && !empty(company_setting('SMS Days Off')) && company_setting('SMS Days Off')  == true) {
            if (!empty($profile->phone)) {
                $uArr = [
                    'date' => $date
                ];
                SendMsg::SendMsgs($profile->phone, $uArr , 'Days Off');

            }
        }
    }
}
