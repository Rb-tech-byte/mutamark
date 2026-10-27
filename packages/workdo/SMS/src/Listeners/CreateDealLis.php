<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Lead\Events\CreateDeal;
use App\Models\User;


class CreateDealLis
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
    public function handle(CreateDeal $event)
    {
        if (module_is_active('SMS') && !empty(company_setting('SMS New Deal')) && company_setting('SMS New Deal')  == true) {
            $request = $event->request;
            $Assign_user_phones = User::whereIn('id', $request->clients)->get();
            foreach ($Assign_user_phones as $Assign_user_phone) {
                if (!empty($Assign_user_phone->mobile_no))
                {
                    $uArr = [];
                    SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Deal');
                }
            }
        }
    }
}
