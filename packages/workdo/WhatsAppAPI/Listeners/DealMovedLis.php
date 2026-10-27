<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Lead\Events\DealMoved;

class DealMovedLis
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
    public function handle(DealMoved $event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Deal Moved')) && company_setting('Whatsappapi Deal Moved')  == true)
        {
            $deal = $event->deal;
            $request = $event->request;
            $newStage = \Modules\Lead\Entities\DealStage::where('id',$request->stage_id)->first();
            $user = \Modules\Lead\Entities\UserDeal::where('deal_id',$deal->id)->get()->pluck('user_id')->toArray();
            $Assign_user_phone = User::whereIn('id', $user)->get();
            foreach($Assign_user_phone as $user)
            if (!empty($user->mobile_no))
            {
                $uArr = [
                    'deal_name' => $deal->name,
                    'old_stage' => $deal->stage->name,
                    'new_stage' => $newStage->name,
                ];
                SendMsg::SendMsgs($user->mobile_no, $uArr , 'Deal moved');
            }
        }
    }
}
