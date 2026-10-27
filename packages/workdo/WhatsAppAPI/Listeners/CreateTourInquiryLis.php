<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\TourTravelManagement\Events\CreateTourInquiry;
use Modules\TourTravelManagement\Entities\Tour;

class CreateTourInquiryLis
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
    public function handle(CreateTourInquiry $event)
    {
        $tour = $event->tour;
        $tourName = Tour::find($tour->tour_id);
        $to = $tour->mobile_no;

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Tour Inquiry')) && company_setting('Whatsappapi New Tour Inquiry')  == true) {

            if(!empty($to))
            {
                $uArr = [
                    'user_name' => $tour->person_name,
                    'tour_name' => $tourName->tour_name
                ];
                SendMsg::SendMsgs($to , $uArr , 'New Tour Inquiry');
    
            }

        }
    }
}
