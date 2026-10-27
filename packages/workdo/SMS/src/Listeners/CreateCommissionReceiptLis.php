<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Commission\Events\CreateCommissionReceipt;


class CreateCommissionReceiptLis
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
    public function handle(CreateCommissionReceipt $event)
    {
        $journal = ($event->journal);
        $to = \Auth::user()->mobile_no;
        if(module_is_active('SMS') && !empty(company_setting('SMS New Commission Receipt')) && company_setting('SMS New Commission Receipt')  == true)
        {
            if(!empty($to))
            {
                $uArr = [
                    'user_name' => $journal->name,
                    'date' => $journal->date
                ];
                SendMsg::SendMsgs($to , $uArr, 'New Commission Receipt');
            }
        }
    }
}
