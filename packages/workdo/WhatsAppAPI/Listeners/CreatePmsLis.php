<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\CMMS\Entities\Part;
use App\Models\User;
use Modules\CMMS\Events\CreatePms;

class CreatePmsLis
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
    public function handle(CreatePms $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Pms')) && company_setting('Whatsappapi New Pms')  == true)
        {
            $request = $event->request;
            $parts_item = Part::find($request->parts);
            
            foreach ($parts_item as $item) {
                $part[] = $item['name'];
            }
            $parts = implode(',', $part);

            $company = User::find($event->pms->company_id);
            $to=\Auth::user()->mobile_no;

            if(!empty($parts) && !empty($to)){
                $uArr = [
                    'part_name' => $parts,                    
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Pms');
            }
        }
    }
}
