<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Recruitment\Events\CreateJobApplication;

class CreateJobApplicationLis
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
    public function handle(CreateJobApplication $event)
    {
        $job = $event->job;
        if(module_is_active('SMS') && !empty(company_setting('SMS New Job Application',$job->created_by,$job->workspace)) && company_setting('SMS New Job Application',$job->created_by,$job->workspace)  == true)
        {
            $request = $event->request;
            if(!empty($request->phone)){
            $uArr = [
                'user_name' => $request->name,
                'job_name' => $job->jobs->title
            ];
            SendMsg::SendMsgs($request->phone, $uArr, 'New Job Application',$job->created_by,$job->workspace);
            }
        }
    }
}
