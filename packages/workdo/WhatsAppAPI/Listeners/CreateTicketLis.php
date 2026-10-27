<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SupportTicket\Events\CreateTicket;

class CreateTicketLis
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
    public function handle(CreateTicket $event)
    {
        $ticket = $event->ticket;
        
        $user = \App\Models\User::where('email' , $ticket->email)->first();

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('WhatsAppAPI New Ticket')) && company_setting('WhatsAppAPI New Ticket')  == true) {
            
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'ticket_name' => $ticket->name,
                    'date' => company_date_formate($ticket->created_at)
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Ticket');
            }

        }
    }
}
