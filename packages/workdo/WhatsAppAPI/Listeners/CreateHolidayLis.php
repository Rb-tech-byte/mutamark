<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use App\Models\User;
use Modules\Hrm\Events\CreateHolidays;

class CreateHolidayLis
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
    public function handle(CreateHolidays $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Holidays')) && company_setting('Whatsappapi New Holidays')  == true)
        {
            $request = $event->request;
            $user = \Auth::user();
            $to = User::where('created_by',$user->id)->where('type','staff')->get();
            foreach($to as $from)
            {
                if(!empty($from->mobile_no)){
                    $uArr = [
                        'name' => $request->occasion,
                        'start_date'=>$request->start_date,
                        'end_date' => $request->end_date
                    ];
                    SendMsg::SendMsgs($from->mobile_no, $uArr , 'New Holidays');
                }
            }
        }
    }
}
