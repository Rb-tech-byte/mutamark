<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ConsignmentManagement\Events\CreatePurchaseOrder;
use App\Models\User;
use Modules\ConsignmentManagement\Entities\Consignment;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Purchase Order')) && company_setting('Whatsappapi New Purchase Order')  == true) {

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
