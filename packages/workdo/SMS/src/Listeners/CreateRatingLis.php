<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Feedback\Events\CreateRating;
use Workdo\Feedback\Entities\TemplateModule;
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
        $usr = \Auth::user();
        if(empty($usr)){
          $usr = User::find($rating->created_by);
        }
        if (module_is_active('SMS') && !empty(company_setting('SMS New Rating' , $rating->created_by , $rating->workspace)) && company_setting('SMS New Rating', $rating->created_by , $rating->workspace)  == true) {

            if(!empty($module) && !empty($user))
            {
                $uArr = [
                    'module_name' => $module->submodule,
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($usr->mobile_no , $uArr , 'New Rating' , $rating->created_by , $rating->workspace);
            }


        }
    }
}
