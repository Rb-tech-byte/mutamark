<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Hrm\Events\CreateMonthlyPayslip;

class CreateMonthlyPayslipLis
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
    public function handle(CreateMonthlyPayslip $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Monthly Payslip')) && company_setting('Whatsappapi New Monthly Payslip')  == true)
        {
            $payslipEmployee = $event->payslipEmployee;
            $request = $event->request;
            $month = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));

            $emp = \Modules\Hrm\Entities\Employee::where('id', $payslipEmployee->employee_id)->first();
            if(!empty($emp->phone)){
                $uArr = [
                    'month'=>$month
                ];
                SendMsg::SendMsgs($emp->phone, $uArr , 'New Monthly Payslip');
            }
        }
    }
}
