<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Retainer\Events\CreateRetainer;

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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Retainer create')) && company_setting('Whatsappapi Retainer create')  == true)
        {
            $retainer = $event->retainer;
            $Assign_user_phone = User::where('id',$retainer->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'retainer_id' => \Modules\Retainer\Entities\Retainer::retainerNumberFormat($retainer->retainer_id),                
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Retainer Create');
            }
        }
    }
}
