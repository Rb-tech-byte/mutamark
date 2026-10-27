<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use App\Events\BankTransferPaymentStatus;
use App\Models\Invoice;
use App\Models\User;
use PDO;
use Workdo\Retainer\Entities\Retainer;

class BankTransferPaymentStatusLis
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
    public function handle(BankTransferPaymentStatus $event)
    {
        if($event->type == 'invoice')
        {
            if (module_is_active('SMS') && !empty(company_setting('SMS Bank Transfer Payment Status Updated')) && company_setting('SMS Bank Transfer Payment Status Updated')  == true)
            {
                $data = $event->data;

                $invoice = Invoice::find($data->invoice_id);

                $user = \Workdo\Account\Entities\Customer::where('user_id',$invoice->user_id)->first();
                if(!empty($user)){
                    $user->mobile_no = $user->contact;
                }
                if(empty($user))
                {
                    $user =User::where('id',$invoice->user_id)->first();
                    $user->mobile_no = $user->mobile_no;
                }

                if(!empty($data) && !empty($user->mobile_no))
                {
                    $uArr = [
                        'invoice_id' => \App\Models\Invoice::invoiceNumberFormat($data->invoice_id)
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'Bank Transfer Payment Status Updated');
                }

            }
        } elseif($event->type == 'retainer')
        {
            if (module_is_active('SMS') && !empty(company_setting('SMS Bank Transfer Payment Status Updated')) && company_setting('SMS Bank Transfer Payment Status Updated')  == true) {

                $data = $event->data;
                $retainer = Retainer::find($data->retainer_id);

                $user = \Workdo\Account\Entities\Customer::where('user_id',$retainer->user_id)->first();
                if(!empty($user)){
                    $user->mobile_no = $user->contact;
                }
                if(empty($user))
                {
                    $user =User::where('id',$retainer->user_id)->first();
                    $user->mobile_no = $user->mobile_no;
                }

                if(!empty($data) && !empty($user->mobile_no))
                {
                    $uArr = [
                        'retainer_id' => \Workdo\Retainer\Entities\Retainer::retainerNumberFormat($data->retainer_id)
                    ];
                    SendMsg::SendMsgs($user->mobile_no , $uArr , 'Bank Transfer Payment Status Updated');
                }

            }
        }
    }
}
