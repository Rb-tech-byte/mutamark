<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SupportTicket\Events\ReplyTicket;

class ReplyTicketLis
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
    public function handle(ReplyTicket $event)
    {

        $ticket = $event->ticket;

        $user = \App\Models\User::where('email' , $ticket->email)->first();

        if (module_is_active('SMS') && !empty(company_setting('SMS New Ticket Reply')) && company_setting('SMS New Ticket Reply')  == true) {

            if(!empty($user->mobile_no))
            {
            $uArr = [
                'user_name' => Auth::user()->name
            ];
            SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Ticket Reply');
        }

        }
    }
}
