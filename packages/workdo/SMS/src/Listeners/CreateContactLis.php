<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\VCard\Events\CreateContact;
use App\Models\User;


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
        if (module_is_active('SMS') && company_setting('sms_notification_is',$contact->created_by, $contact->workspace) == 'on' && !empty(company_setting('SMS New Contact',$contact->created_by, $contact->workspace)) && company_setting('SMS New Appointment',$contact->created_by, $contact->workspace) == true)
        {
            $to = User::find($contact->created_by)->mobile_no;
            if (!empty($to)) {
                $business_name   = \Workdo\VCard\Entities\Business::where('id',$contact->business_id)->pluck('title')->first();
                $uArr = [
                    'contact_name' => $contact->name,
                    'business_name' => $business_name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Contact',$contact->created_by, $contact->workspace);
            }

        }
    }
}
