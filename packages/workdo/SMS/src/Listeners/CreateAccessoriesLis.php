<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FixEquipment\Events\CreateAccessories;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        if (module_is_active('SMS') && !empty(company_setting('SMS New Accessories')) && company_setting('SMS New Accessories')  == true) {
            $to = Auth::user()->mobile_no;
            $uArr = [
                'name' => $accessories->title,
                'supplier_name' => $supplier->name
            ];
            SendMsg::SendMsgs($to ,$uArr , 'New Accessories');

        }
    }
}
