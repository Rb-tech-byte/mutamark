<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\VisitorManagement\Events\CreateVisitor;
use Illuminate\Support\Facades\Auth;

class CreateVisitorLis
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
    public function handle(CreateVisitor $event)
    {
        $visitor = $event->visitor;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Visitor')) && company_setting('SMS New Visitor')  == true) {
         if(!empty($visitor->phone)){

             $uArr = [
                 'name' => $visitor->first_name . $visitor->last_name,
             ];
             $to =$visitor->phone;
             SendMsg::SendMsgs($to,$uArr , 'New Visitor');
         }

        }
    }
}
