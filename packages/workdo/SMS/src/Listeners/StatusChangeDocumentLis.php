<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Documents\Events\StatusChangeDocument;
use App\Models\User;
class StatusChangeDocumentLis
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
    public function handle(StatusChangeDocument $event)
    {
        $documents = $event->documents;
        $user = User::find($documents->user_id);
        $company = User::find($user->created_by);
        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS Update Status Document')) && company_setting('SMS Update Status Document')  == true) {

            if(!empty($company->mobile_no))
            {
                $uArr = [
                    'status' => $documents->status,
                    'user_name' => !empty($user) ? $user->name : '-'
                ];
                SendMsg::SendMsgs($company->mobile_no , $uArr , 'Update Status Document');
            }
        }
    }
}
