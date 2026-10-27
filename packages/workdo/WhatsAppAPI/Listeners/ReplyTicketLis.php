<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SupportTicket\Events\ReplyTicket;
use Illuminate\Support\Facades\Auth;

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
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('WhatsAppAPI New Ticket Reply')) && company_setting('WhatsAppAPI New Ticket Reply')  == true) {

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
