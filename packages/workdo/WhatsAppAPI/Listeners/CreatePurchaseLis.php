<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;
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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Purchase')) && company_setting('Whatsappapi New Purchase')  == true)
        {
            $request = $event->request;
            $purchase = $event->purchase;
            $vender = \Modules\Account\Entities\Vender::where('id',$request->vender_id)->first();
            if(!empty($vender))
            {
                $moblie = $vender->contact;
            }
            else
            {
                $user = User::where('id',$request->vender_id)->first();
                $moblie = $user->mobile_no;
            }
            if(!empty($moblie))
            {
                $uArr = [
                    'purchase_id' => \App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id),
                ];
                SendMsg::SendMsgs($moblie , $uArr, 'New Purchase');
            }
        }
    }
}
