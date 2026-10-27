<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Sales\Events\CreateQuote;

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
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Quote')) && company_setting('Whatsappapi New Quote')  == true)
        {
            $quote = $event->quote;
            $Assign_user_phone = User::where('id',$quote->user_id)->first();
            if(!empty($Assign_user_phone->mobile_no))
            {
                $uArr = [
                    'quotation_id' => $quote->quoteNumberFormat(\Modules\Sales\Http\Controllers\QuoteController::quoteNumber())
                ];
                SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr , 'New Quote');
            }
        }
    }
}
