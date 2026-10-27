<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ConsignmentManagement\Events\CreatePurchaseOrder;
use App\Models\User;
use Workdo\ConsignmentManagement\Entities\Consignment;

class CreatePurchaseOrderLis
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
    public function handle(CreatePurchaseOrder $event)
    {
        $purchaseOrder = $event->purchaseOrder;
        $consignment = Consignment::find($purchaseOrder->consignment_id);
        $vendor = User::find($purchaseOrder->vendor_id);

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Purchase Order')) && company_setting('SMS New Purchase Order')  == true) {

            if(!empty($consignment) && !empty($vendor) && !empty($vendor->mobile_no))
            {
                $uArr = [
                    'consignment_name' => $consignment->title,
                    'vender_name' => $vendor->name
                ];
                SendMsg::SendMsgs($vendor->mobile_no , $uArr , 'New Purchase Order');
            }
        }
    }
}
