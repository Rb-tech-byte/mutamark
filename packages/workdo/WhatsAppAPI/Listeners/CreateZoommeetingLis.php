<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\ZoomMeeting\Events\CreateZoommeeting;

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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Zoom Meeting')) && company_setting('Whatsappapi New Zoom Meeting')  == true)
        {
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
