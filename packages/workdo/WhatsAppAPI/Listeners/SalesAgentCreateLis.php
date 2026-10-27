<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\SalesAgent\Events\SalesAgentCreate;
use App\Models\User;
use Modules\SalesAgent\Entities\Customer;

class SalesAgentCreateLis
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
    public function handle(SalesAgentCreate $event)
    {
        $salesagent = $event->salesagent;

        $user = User::find($salesagent->created_by);
        $customer = Customer::where('customer_id', $salesagent->customer_id)->first();

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Sales Agent')) && company_setting('Whatsappapi New Sales Agent')  == true) {

            if(!empty($user) && !empty($customer->contact))
            {
                $uArr = [
                    'name' => $customer->name,
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($customer->contact , $uArr , 'New Sales Agent');
            }


        }
    }
}
