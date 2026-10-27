<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Contract\Events\CreateContract;
use App\Models\User;


class CreateContractLis
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
    public function handle(CreateContract $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Contract')) && company_setting('SMS New Contract')  == true)
        {
            $contract = $event->contract;
            $Assign_user_phone = User::where('id',$contract->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'contract_number' => $contract::contractNumberFormat(\Workdo\Contract\Http\Controllers\ContractController::contractNumber())
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Contract');
            }
        }
    }
}
