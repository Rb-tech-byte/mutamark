<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Hrm\Events\CreateCompanyPolicy;

class CreateCompanyPolicyLis
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
    public function handle(CreateCompanyPolicy $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Company Policy')) && company_setting('Whatsappapi New Company Policy')  == true)
        {
            $request = $event->request;
            $policy = $event->policy;
            $to=\Auth::user();
            $branch = \Modules\Hrm\Entities\Branch::find($request->branch);
            $user = \Modules\Hrm\Entities\Employee::where('created_by',$to->id)->where('branch_id',$branch->id)->get();
            foreach($user as $emp){
                if(!empty($emp->phone)){
                    $uArr = [
                        'name' => $request->title,
                        'branch_name' => $policy->branches->name
                    ];
                    SendMsg::SendMsgs($emp->phone, $uArr , 'New Company Policy');
                }
            }
        }
    }
}
