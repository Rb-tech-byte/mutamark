<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Fleet\Events\CreateInsurance;

class CreateInsuranceLis
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
    public function handle(CreateInsurance $event)
    {
        $insurance = $event->insurance;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Insurance')) && company_setting('SMS New Insurance')  == true) {

            $uArr = [
                'insurance_provider' => $insurance->insurance_provider,
            ];
            $to = Auth::user()->mobile_no;

            SendMsg::SendMsgs($to,$uArr , 'New Insurance');
        }
    }
}
