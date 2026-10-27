<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\PropertyManagement\Events\CreatePropertyInvoice;
use App\Models\User;
class CreatePropertyInvoiceLis
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
    public function handle(CreatePropertyInvoice $event)
    {
        $propertyInvoice = $event->propertyInvoice;
        $user = User::find($propertyInvoice->user_id);
        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Property Invoice')) && company_setting('SMS New Property Invoice')  == true) {

            if(!empty($user) && !empty($propertyInvoice) && !empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Property Invoice');
            }
        }
    }
}
