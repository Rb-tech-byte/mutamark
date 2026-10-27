<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Retainer\Events\CreateRetainer;
use App\Models\User;


class CreateRetainerLis
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
    public function handle(CreateRetainer $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS Retainer create')) && company_setting('SMS Retainer create')  == true)
        {
            $retainer = $event->retainer;
            $Assign_user_phone = User::where('id',$retainer->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'retainer_id' => \Workdo\Retainer\Entities\Retainer::retainerNumberFormat($retainer->retainer_id),
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Retainer Create');
            }
        }
    }
}
