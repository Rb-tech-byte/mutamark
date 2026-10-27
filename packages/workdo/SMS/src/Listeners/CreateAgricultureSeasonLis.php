<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\AgricultureManagement\Events\CreateAgricultureSeason;
use Workdo\AgricultureManagement\Entities\AgricultureSeasonType;
use Illuminate\Support\Facades\Auth;


class CreateAgricultureSeasonLis
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
    public function handle(CreateAgricultureSeason $event)
    {
        $agricultureseason = $event->agricultureseason;

        if (module_is_active('SMS') && !empty(company_setting('SMS New Agriculture Season')) && company_setting('SMS New Agriculture Season')  == true) {

            if(!empty($agricultureseason))
            {
            $season = AgricultureSeasonType::where('id',$agricultureseason->season)->where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->first();

                $uArr = [
                    'season_name' => $agricultureseason->name,
                    'season' => $season->name,
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to,$uArr , 'New Agriculture Season');
            }
        }
    }
}
