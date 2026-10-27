<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Hrm\Events\CreateAward;

class CreateAwardLis
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
    public function handle(CreateAward $event)
    {
        //twilio
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Award')) && company_setting('Whatsappapi New Award')  == true)
        {
            $request = $event->request;
            $award = $event->award;
            $emp = User::find($request->employee_id);
            if(!empty($emp->mobile_no)){
                $uArr = [
                    'award_name' => $award->awardType->name,
                    'user_name' => $emp->name,
                    'date' => $request->date
                ];
                SendMsg::SendMsgs($emp->mobile_no, $uArr , 'New Award');
            }
        }
    }
}
