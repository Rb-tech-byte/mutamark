<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Documents\Events\CreateDocuments;
use App\Models\User;
class CreateDocumentsLis
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
    public function handle(CreateDocuments $event)
    {

        $documents = $event->documents;
        $user = User::find($documents->user_id);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Document')) && company_setting('SMS New Document')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $documents->subject,
                    'user_name' => !empty($user) ? $user->name : '-'
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Document');
            }
        }
    }
}
