<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Feedback\Events\CreateRating;
use Modules\Feedback\Entities\TemplateModule;
use App\Models\User;

class CreateRatingLis
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
    public function handle(CreateRating $event)
    {
        $rating = $event->rating;
        $module = TemplateModule::find($rating->module_id);

        $user = (json_decode($rating->content));
        $authUser = User::find($rating->created_by);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is', $rating->created_by , $rating->workspace)=='on'  && !empty(company_setting('Whatsappapi New Feedback Rating' , $rating->created_by , $rating->workspace)) && company_setting('Whatsappapi New Feedback Rating', $rating->created_by , $rating->workspace)  == true) {

            if(!empty($module) && !empty($user) && !empty($authUser->mobile_no))
            {
                $uArr = [
                    'module_name' => $module->submodule,
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($authUser->mobile_no , $uArr , 'New Feedback Rating' , $rating->created_by , $rating->workspace);
            }


        }
    }
}
