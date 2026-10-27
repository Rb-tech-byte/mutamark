<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use App\Models\Purchase;
use App\Events\CreatePurchase;

class CreatePurchaseLis
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
    public function handle(CreatePurchase $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Purchase')) && company_setting('SMS New Purchase')  == true)
        {
            $request = $event->request;
            $purchase = $event->purchase;
            $Assign_user_phone = \Workdo\Account\Entities\Vender::where('user_id',$request->vender_id)->first();
            if(!empty($Assign_user_phone->contact))
            {
                $uArr = [
                    'purchase_id' => \App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id),
                ];
                SendMsg::SendMsgs($Assign_user_phone->contact , $uArr, 'New Purchase');
            }
        }
    }
}
