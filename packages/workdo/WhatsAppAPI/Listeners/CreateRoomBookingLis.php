<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Holidayz\Entities\RoomBooking;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Holidayz\Events\CreateRoomBooking;


class CreateRoomBookingLis
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
    public function handle(CreateRoomBooking $event)
    {
        $booking = $event->booking;
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is' , $booking->created_by , $booking->workspace)=='on' 
        && !empty(company_setting('Whatsappapi New Room Booking',$booking->created_by,$booking->workspace)) && company_setting('Whatsappapi New Room Booking',$booking->created_by,$booking->workspace)  == true)
        {
            if(!empty(\Auth::guard('holiday')->user()) || !empty(\Auth::user()))
            {
                
                if(!empty(\Auth::guard('holiday')->user())){
                    $booking = $event->booking;
                    $Assign_user_phone = User::where('active_workspace',$booking->workspace)->first();
                    $customer = \Modules\Holidayz\Entities\HotelCustomer::find($booking->user_id);
                    if(!empty($Assign_user_phone->mobile_no))
                    {

                        $uArr = [
                            'booking_number' => RoomBooking::bookingNumberFormat($booking->booking_number),
                            'user_name' => $customer->name
                        ];
                        
                        SendMsg::SendMsgs($Assign_user_phone->mobile_no,$uArr , 'New Room Booking');
                    }
                }else{
                    $booking = $event->booking;
                    $Assign_user_phone = User::where('active_workspace',$booking->workspace)->first();
                    $customer = \Modules\Holidayz\Entities\HotelCustomer::find($booking->user_id);
                    if(!empty($customer->mobile_phone))
                    {
                        
                        $uArr = [
                            'booking_number' => RoomBooking::bookingNumberFormat($booking->booking_number),
                            'user_name' => $Assign_user_phone->name
                        ];
                        SendMsg::SendMsgs($customer->mobile_phone ,$uArr , 'New Room Booking');
                    }
                }
            }else{

                $booking = $event->booking;
                $Assign_user_phone = User::where('active_workspace',$booking->workspace)->first();
                if(!empty($Assign_user_phone->mobile_no))
                {
                    $uArr = [
                        'booking_number' => RoomBooking::bookingNumberFormat($booking->booking_number),
                        'user_name' => $booking->first_name
                    ];
                    SendMsg::SendMsgs($Assign_user_phone->mobile_no ,$uArr , 'New Room Booking');     // $booking->phone
                }
            }
        }
    }
}
