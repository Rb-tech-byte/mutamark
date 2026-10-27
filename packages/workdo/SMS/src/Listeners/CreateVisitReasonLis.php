<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\VisitorManagement\Events\CreateVisitReason;
use Illuminate\Support\Facades\Auth;

class CreateVisitReasonLis
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
    public function handle(CreateVisitReason $event)
    {
        $visitorReason = $event->visitorReason;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Visit Reason')) && company_setting('SMS New Visit Reason')  == true) {


            $uArr = [
                'name' => $visitorReason->reason
            ];
            $to = Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $uArr , 'New Visit Reason');

        }
    }
}
