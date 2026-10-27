<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Internalknowledge\Events\CreateBook;
use App\Models\User;
class CreateBookLis
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
    public function handle(CreateBook $event)
    {
        $book = $event->book;
        $users = User::whereIn('id', explode(',', $book->user_id))->get();

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Book')) && company_setting('SMS New Book')  == true) {

            foreach ($users as $user) {
                if(!empty($user->mobile_no))
                {
                    $uArr = [
                        'name' => $book->title,
                        'user_name' => $user->name
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Book');
                }
            }
        }

    }
}
