<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Lead\Events\DealMoved;
use App\Models\User;



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
        if (module_is_active('SMS') && !empty(company_setting('SMS Deal Moved')) && company_setting('SMS Deal Moved')  == true)
        {
            $deal = $event->deal;
            $request = $event->request;

            $newStage = \Workdo\Lead\Entities\DealStage::where('id',$request->stage_id)->first();
            $user = \Workdo\Lead\Entities\UserDeal::where('deal_id',$deal->id)->get()->pluck('user_id');
            $Assign_user_phone = User::whereIn('id', $user)->first();

            if (!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'deal_name' => $deal->name,
                    'old_stage' => $deal->stage->name,
                    'new_stage' => $newStage->name,
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'Deal moved');
            }
        }
    }
}
