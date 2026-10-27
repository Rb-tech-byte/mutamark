<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Recruitment\Events\CreateJob;
use Illuminate\Support\Facades\Auth;


class CreateJobLis
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
    public function handle(CreateJob $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Job')) && company_setting('SMS New Job')  == true)
        {
            $job = $event->job;
            if(!empty($job))
            {
                $uArr = [
                    'job_name' => $job->title,
                ];
                $to = Auth::user()->mobile_no;

                SendMsg::SendMsgs($to,$uArr, 'New Job');
            }
        }
    }
}
