<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;

class CompanyPaymentLis
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
    public function handle($event)
    {
        $type = $event->type;
        $payment = $event->payment;
        $data = $event->data;
        if($type == 'invoice')
        {
            if(!empty($data))
            {
                if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi Invoice Status Updated', $data->created_by, $data->workspace)) && company_setting('Whatsappapi Invoice Status Updated', $data->created_by, $data->workspace)  == true)
                {
                    $Assign_user_phone = User::where('id', $data->created_by)->first();
                    if (!empty($Assign_user_phone->mobile_no))
                    {
                        $uArr = [
                            'amount' => company_setting('defult_currancy_symbol',$data->created_by,$data->workspace).$payment->amount,
                            'user_name' => $data->customer->name,
                            'payment_type'=> $payment->payment_type
                        ];
                        SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr, 'Invoice Status Updated', $data->created_by,$data->workspace);
                    }
                }
            }
        }
        elseif($type == 'salesinvoice')
        {
            if(!empty($data))
            {
                if (module_is_active('Whatsapp') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Sales Invoice Payment', $data->created_by, $data->workspace)) && company_setting('Whatsappapi New Sales Invoice Payment', $data->created_by, $data->workspace)  == true)
                {
                    $Assign_user_phone = User::where('id', $data->created_by)->first();
                    if (!empty($Assign_user_phone->mobile_no))
                    {
                        $uArr = [
                            'amount' => company_setting('defult_currancy_symbol',$data->created_by,$data->workspace).$payment->amount,
                            'user_name' => $data->assign_user->name,
                            'payment_type'=> $payment->payment_type
                        ];
                        SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr, 'New Sales Invoice Payment', $data->created_by,$data->workspace);
                    }
                }
            }
        }
        elseif($type == 'retainer')
        {
            if(!empty($data))
            {
                if(module_is_active('Whatsapp') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Retainer Payment',$data->created_by,$data->workspace)) && company_setting('Whatsappapi New Retainer Payment',$data->created_by,$data->workspace)  == true)
                {
                    $Assign_user_phone = User::where('id',$data->created_by)->first();
                    if(!empty($Assign_user_phone->mobile_no))
                    {
                        $uArr = [
                            'amount' => company_setting('defult_currancy_symbol',$data->created_by,$data->workspace).$payment->amount,
                            'user_name' => $data->customer->name,
                            'payment_type'=> $payment->payment_type
                        ];
                        SendMsg::SendMsgs($Assign_user_phone->mobile_no, $uArr, 'New Retainer Payment', $data->created_by,$data->workspace);
                    }
                }
            }
        }

    }
}
