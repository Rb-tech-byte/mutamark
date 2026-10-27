<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Commission\Events\CreateCommissionPlan;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;

class CreateCommissionPlanLis
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
    public function handle(CreateCommissionPlan $event)
    {        
        $commissionPlan = ($event->commissionPlan);
        $users = User::whereIn('id', explode(',',$commissionPlan->user_id))->get()->pluck('name','id');
        
        $user_detail = [];
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_detail[] = $user;
            }
        }
        $users = implode(',', $user_detail);
        $user = User::where('id',$commissionPlan->created_by)->first();

        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Commision Plan')) && company_setting('Whatsappapi New Commision Plan')  == true)
        {
            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name' => $commissionPlan->name,
                    'user_name' => $users,
                    'start_date' => $commissionPlan->start_date,
                    'end_date' => $commissionPlan->end_date,
                    
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr, 'New Commision Plan');
            }
        }

    }
}
