<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Portfolio\Events\CreatePortfolio;
use Modules\Portfolio\Entities\PortfolioCategory;
use App\Models\User;

class CreatePortfolioLis
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
    public function handle(CreatePortfolio $event)
    {
        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' 
        && !empty(company_setting('Whatsappapi New Portfolio')) && company_setting('Whatsappapi New Portfolio')  == true)
        {
            $portfolio = $event->portfolio;
            $category =  PortfolioCategory::find($portfolio->category);
            $user = User::where('id',$portfolio->created_by)->first();

            if (!empty($user->mobile_no)) {
                $uArr = [
                    'portfolio_name' => $portfolio->title,
                    'portfolio_category' => $category->title,
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Portfolio');
            }
        }
    }
}
