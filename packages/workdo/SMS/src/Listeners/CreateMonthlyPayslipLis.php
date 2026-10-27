<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Hrm\Events\CreateMonthlyPayslip;

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
        if(module_is_active('SMS') && !empty(company_setting('SMS New Monthly Payslip')) && company_setting('SMS New Monthly Payslip')  == true)
        {
            $payslipEmployee = $event->payslipEmployee;
            $request = $event->request;
            $month = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));

            $emp = \Workdo\Hrm\Entities\Employee::where('id', $payslipEmployee->employee_id)->first();
            if(!empty($emp->phone)){
                $uArr = [
                    'month'=>$month
                ];
                SendMsg::SendMsgs($emp->phone, $uArr , 'New Monthly Payslip');
            }
        }
    }
}
