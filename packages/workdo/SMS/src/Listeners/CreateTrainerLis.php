<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Training\Events\CreateTrainer;

class CreateTrainerLis
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
    public function handle(CreateTrainer $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Trainer')) && company_setting('SMS New Trainer')  == true)
        {
            $request = $event->request;
            $trainer = $event->trainer;
            if(!empty($request->contact)){
                $uArr = [
                    'branch_name' => $trainer->branches->name
                ];
                SendMsg::SendMsgs($request->contact, $uArr , 'New Trainer');
            }
        }
    }
}
