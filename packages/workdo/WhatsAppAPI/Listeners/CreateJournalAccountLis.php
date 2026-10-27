<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\DoubleEntry\Events\CreateJournalAccount;
use Modules\DoubleEntry\Entities\JournalItem;
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

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Journal Entry')) && company_setting('Whatsappapi New Journal Entry')  == true)
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
