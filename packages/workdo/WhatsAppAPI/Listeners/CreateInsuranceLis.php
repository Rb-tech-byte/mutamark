<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Fleet\Events\CreateInsurance;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Insurance')) && company_setting('Whatsappapi New Insurance')  == true) {
            
            $to = Auth::user()->mobile_no;
            if(!empty($insurance) && !empty($to))
            {
                $uArr = [
                    'insurance_provider' => $insurance->insurance_provider,
                ];
    
                SendMsg::SendMsgs($to,$uArr , 'New Insurance');
            }

        }
    }
}
