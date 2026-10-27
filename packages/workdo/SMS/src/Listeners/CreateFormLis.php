<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\FormBuilder\Events\CreateForm;

class CreateFormLis
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
    public function handle(CreateForm $event)
    {
        $form_builder = $event->form_builder;
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Form')) && company_setting('SMS New Form')  == true) {

            if(!empty($form_builder) && !empty($to))
            {
                $uArr = [
                    'name' => $form_builder->name
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Form');
            }

        }
    }
}
