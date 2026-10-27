<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ConsignmentManagement\Events\CreateSaleOrder;
use App\Models\User;
use Workdo\ConsignmentManagement\Entities\Consignment;

class CreateSaleOrderLis
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
    public function handle(CreateSaleOrder $event)
    {
        $saleOrder = $event->saleOrder;
        $consignment = Consignment::find($saleOrder->consignment_id);
        $user = User::find($saleOrder->customer_id);

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Sale Order')) && company_setting('SMS New Sale Order')  == true) {

            if(!empty($consignment) && !empty($user) && !empty($user->mobile_no))
            {
                $uArr = [
                    'consignment_name' => $consignment->title,
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Sale Order');
            }

        }
    }
}
