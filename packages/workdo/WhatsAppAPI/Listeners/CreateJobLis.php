<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Recruitment\Events\CreateJob;
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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Job')) && company_setting('Whatsappapi New Job')  == true)
        {
            $job = $event->job;
            $to = Auth::user()->mobile_no;
            if(!empty($job) && !empty($to))
            {
                $uArr = [
                    'job_name' => $job->title,
                ];

                SendMsg::SendMsgs($to,$uArr, 'New Job');
            }
        }
    }
}
