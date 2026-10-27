<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Portfolio\Events\UpdatePortfolioStatus;
use Workdo\Portfolio\Entities\Portfolio;
use App\Models\User;

class UpdatePortfolioStatusLis
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
    public function handle(UpdatePortfolioStatus $event)
    {
        $itemId = $event->request->input('catId');
        $item = Portfolio::find($itemId);
        $user = User::where('id',$item->created_by)->first();

        if (module_is_active('SMS') && !empty(company_setting('SMS Update Portfolio Status')) && company_setting('SMS Update Portfolio Status')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'portfolio_name' => $item->title,
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'Update Portfolio Status');
            }


        }
    }
}
