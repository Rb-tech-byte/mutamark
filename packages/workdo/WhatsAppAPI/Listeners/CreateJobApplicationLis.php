<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Recruitment\Events\CreateJobApplication;

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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Job Application',$job->created_by,$job->workspace)) && company_setting('Whatsappapi New Job Application',$job->created_by,$job->workspace)  == true)
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
