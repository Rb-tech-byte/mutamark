<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Sales\Events\CreateSalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CreateSalesOrderLis
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
    public function handle(CreateSalesOrder $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Sales Order')) && company_setting('SMS New Sales Order')  == true)
        {
            $salesorder = $event->salesorder;
            $Assign_user_phone = User::where('id',$salesorder->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'sales_order_id' => $salesorder->salesorderNumberFormat(\Workdo\Sales\Http\Controllers\SalesOrderController::salesorderNumber())
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Sales Order');
            }
        }
    }
}
