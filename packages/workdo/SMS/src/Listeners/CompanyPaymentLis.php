<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use App\Models\User;


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
                if (module_is_active('SMS') && !empty(company_setting('SMS Invoice Status Updated', $data->created_by, $data->workspace)) && company_setting('SMS Invoice Status Updated', $data->created_by, $data->workspace)  == true)
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
                if (module_is_active('SMS') && !empty(company_setting('SMS New Sales Invoice Payment', $data->created_by, $data->workspace)) && company_setting('SMS New Sales Invoice Payment', $data->created_by, $data->workspace)  == true)
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
                if(module_is_active('SMS') && !empty(company_setting('SMS New Retainer Payment',$data->created_by,$data->workspace)) && company_setting('SMS New Retainer Payment',$data->created_by,$data->workspace)  == true)
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
        elseif($type == 'roombookinginvoice')
        {
            if(!empty($data))
            {
                if(module_is_active('SMS') && !empty(company_setting('SMS New Room Booking Invoice Payment',$data->created_by,$data->workspace)) && company_setting('SMS New Room Booking Invoice Payment',$data->created_by,$data->workspace)  == true)
                {
                    if(!empty(\Auth::guard('holiday')->user()))
                    {
                        $Assign_user_phone = User::where('id',$data->created_by)->first();
                        $customer = \Workdo\Holidayz\Entities\HotelCustomer::find($payment->user_id);
                        if(!empty($Assign_user_phone->mobile_no))
                        {
                            $msg = __("A new payment of "). company_setting('defult_currancy_symbol',$data->created_by,$data->workspace).$payment->total .__(" has been created by "). $customer->name .__(" via "). $payment->payment_method;

                            SendMsg::SendMsgs($Assign_user_phone->mobile_no,$msg,$data->created_by,$data->workspace);
                        }
                    }else{

                        $Assign_user_phone = User::where('id',$data->created_by)->first();
                        if(!empty($Assign_user_phone->mobile_no))
                        {
                            $msg = __("A new payment of "). company_setting('defult_currancy_symbol',$data->created_by,$data->workspace).$payment->total .__(" has been created by "). $payment->first_name .__(" via "). $payment->payment_method;

                            SendMsg::SendMsgs($Assign_user_phone->mobile_no,$msg,$data->created_by,$data->workspace);
                        }
                    }
                }
            }
        }

    }
}
