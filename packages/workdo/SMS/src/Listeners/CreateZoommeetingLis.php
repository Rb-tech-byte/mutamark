<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ZoomMeeting\Events\CreateZoommeeting;
use App\Models\User;

class CreateZoommeetingLis
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
    public function handle(CreateZoommeeting $event)
    {
        $new = $event->new;
        $request = $event->request;
        $name = $new->title;
        $date = $new->start_date;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Zoom Meeting')) && company_setting('SMS New Zoom Meeting')  == true) {
            $users = User::whereIN('id', $request->users)->get();
            foreach ($users as $user) {
                if (!empty($user->mobile_no)) {
                    $uArr = [
                        'meeting_name' => $name,
                        'user_name' => $name,
                        'date' => $date
                    ];
                    SendMsg::SendMsgs($user->mobile_no, $uArr , 'New Zoom Meeting');
                }
            }
        }
    }
}
