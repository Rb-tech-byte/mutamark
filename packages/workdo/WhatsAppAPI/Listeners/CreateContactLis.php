<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\VCard\Events\CreateContact;

class CreateContactLis
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
    public function handle(CreateContact $event)
    {
        $request = $event->request;
        $contact = $event->contact;
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is',$contact->created_by, $contact->workspace)=='on' && !empty(company_setting('Whatsappapi New Contact',$contact->created_by, $contact->workspace)) && company_setting('Whatsappapi New Contact',$contact->created_by, $contact->workspace) == true) {

            $to = \Auth::user()->mobile_no;
            if (!empty($to)) {
                $business_name   = \Modules\VCard\Entities\Business::where('id',$contact->business_id)->pluck('title')->first();
                $uArr = [
                    'contact_name' => $request->name,
                    'business_name' => $business_name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Contact');
            }

        }
    }
}
