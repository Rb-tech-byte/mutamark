<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Events\CreateAward;
use App\Models\User;

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
        if(module_is_active('SMS') && !empty(company_setting('SMS New Award')) && company_setting('SMS New Award')  == true)
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
