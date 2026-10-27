<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\InnovationCenter\Events\CreateCategory;
use App\Models\User;
class CreateCategoryLis
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
    public function handle(CreateCategory $event)
    {
        $CreativityCategories = $event->CreativityCategories;
        $user = User::find($CreativityCategories->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Category')) && company_setting('SMS New Category')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'name'=> $CreativityCategories->title
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Category');
            }

        }
    }
}
