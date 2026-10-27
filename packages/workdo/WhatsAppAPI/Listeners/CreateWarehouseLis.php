<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Events\CreateWarehouse;


class CreateWarehouseLis
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
    public function handle(CreateWarehouse $event)
    {
        $warehouse = $event->warehouse;

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Warehouse')) && company_setting('Whatsappapi New Warehouse')  == true)
        {
            $to = \Auth::user()->mobile_no;

            if(!empty($warehouse) && !empty($to))
            {
                $uArr = [
                    'warehouse_name' => $warehouse->name,
                ];

                SendMsg::SendMsgs($to , $uArr, 'New Warehouse');
            }
        }
    }
}
