<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\DoubleEntry\Events\CreateJournalAccount;
use Workdo\DoubleEntry\Entities\JournalItem;
use App\Models\User;
class CreateJournalAccountLis
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
    public function handle(CreateJournalAccount $event)
    {

        $journal = ($event->journal);

        $user = User::where('id',$journal->created_by)->first();
        if(module_is_active('SMS') && !empty(company_setting('SMS New Journal Entry')) && company_setting('SMS New Journal Entry')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'date' => $journal->date
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Journal Entry');
            }
        }

    }
}
