<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Holidayz\Entities\RoomBooking;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Holidayz\Events\CreateRoomBooking;
use App\Models\User;


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
        if(module_is_active('SMS') && !empty(company_setting('SMS New Room Booking',$booking->created_by,$booking->workspace)) && company_setting('SMS New Room Booking',$booking->created_by,$booking->workspace)  == true)
        {
            if(!empty(\Auth::guard('holiday')->user()) || !empty(\Auth::user()))
            {

                if(!empty(\Auth::guard('holiday')->user())){
                    $booking = $event->booking;
                    $Assign_user_phone = User::where('active_workspace',$booking->workspace)->first();
                    $customer = \Workdo\Holidayz\Entities\HotelCustomer::find($booking->user_id);
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
                    $customer = \Workdo\Holidayz\Entities\HotelCustomer::find($booking->user_id);
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
