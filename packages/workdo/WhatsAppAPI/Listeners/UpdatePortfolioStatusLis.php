<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Portfolio\Events\UpdatePortfolioStatus;
use Modules\Portfolio\Entities\Portfolio;
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

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi Update Portfolio Status')) && company_setting('Whatsappapi Update Portfolio Status')  == true) {

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
