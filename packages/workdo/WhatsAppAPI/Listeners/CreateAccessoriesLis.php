<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\FixEquipment\Events\CreateAccessories;
use App\Models\User;

class CreateAccessoriesLis
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
    public function handle(CreateAccessories $event)
    {
        $accessories = $event->accessories;
        $supplier = User::find($accessories->supplier);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Accessories')) && company_setting('Whatsappapi New Accessories')  == true) {

            if(!empty($supplier->mobile_no))
            {
                $uArr = [
                    'name' => $accessories->title,
                    'supplier_name' => $supplier->name
                ];
                SendMsg::SendMsgs($supplier->mobile_no , $uArr , 'New Accessories');
            }


        }
    }
}
