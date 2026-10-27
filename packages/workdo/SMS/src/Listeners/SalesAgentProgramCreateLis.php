<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\SalesAgent\Events\SalesAgentProgramCreate;
use Workdo\SalesAgent\Entities\Customer;

use App\Models\User;


class SalesAgentProgramCreateLis
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
    public function handle(SalesAgentProgramCreate $event)
    {
        $program = $event->program;
        $user = User::find($program->created_by);
        if (module_is_active('SMS') && !empty(company_setting('SMS New Program')) && company_setting('SMS New Program')  == true) {
        if(module_is_active('Account')){
            $users = Customer::whereIn('user_id' , explode(',' , $program->sales_agents_applicable))->get();
            foreach($users as $user_no)
            {

                if(!empty($program) && !empty($user) && !empty($user_no->contact))
                {
                    $uArr = [
                        'program_name' => $program->name,
                        'user_name'    => $user->name,
                        'start_date'   => $program->from_date,
                        'end_date'     => $program->to_date
                    ];
                    SendMsg::SendMsgs($user_no->contact , $uArr , 'New Program');
                }
            }
        }else{
            $users = User::whereIn('id' , explode(',' , $program->sales_agents_applicable))->get();

            foreach($users as $user_no)
            {

                if(!empty($program) && !empty($user) && !empty($user_no->mobile_no))
                {
                    $uArr = [
                        'program_name' => $program->name,
                        'user_name'    => $user->name,
                        'start_date'   => $program->from_date,
                        'end_date'     => $program->to_date
                    ];
                    SendMsg::SendMsgs($user_no->mobile_no , $uArr , 'New Program');
                }
            }
        }



        }
    }
}
