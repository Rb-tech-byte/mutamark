<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Sales\Events\CreateQuote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class CreateQuoteLis
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
    public function handle(CreateQuote $event)
    {
        if(module_is_active('SMS') && !empty(company_setting('SMS New Quote')) && company_setting('SMS New Quote')  == true)
        {
            $quote = $event->quote;
            $Assign_user_phone = User::where('id',$quote->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {

                $uArr = [
                    'quotation_id' => $quote->quoteNumberFormat(\Workdo\Sales\Http\Controllers\QuoteController::quoteNumber())
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Quote');
            }
        }
    }
}
