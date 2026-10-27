<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Booking;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('fleetavailability manage')) {
            if (\Auth::user()->type == "company") {
                $bookings = Booking::where('created_by', '=', creatorId())->where('workspace', '=', getActiveWorkSpace())->get();
            } else {
                $bookings = Booking::where('created_by', '=', creatorId())->where('workspace', '=', getActiveWorkSpace())->where('customer_name', \Auth::user()->id)->get();
            }
            $arrEvents = [];
            foreach ($bookings as $booking) {
                $arr['id']              = $booking['id'];
                $arr['start']           = $booking['start_date'];
                $arr['end']             = $booking['end_date'];
                $arr['notes']           = $booking['notes'];
                $arr['url']             = route('availability.show', $booking['id']);

                if ($booking->status == 'Yet to start') {
                    $bg_color = 'event-warning';
                } elseif ($booking->status == 'Completed') {
                    $bg_color = 'event-success';
                } elseif ($booking->status == 'OnGoing') {
                    $bg_color = 'event-info';
                } else {
                    $bg_color = 'event-danger';
                }
                $arr['className']       = $bg_color;
                $arrEvents[] = $arr;


            }

            $calenderArray = array_merge($arrEvents);
            $calenderDatas  = json_encode($calenderArray);

            return view('fleet::availability.index', compact('arrEvents', 'calenderDatas'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return redirect()->back();
        return view('fleet::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return redirect()->back();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (Auth::user()->isAbleTo('fleetavailability show')) {

            $booking = Booking::find($id);

        return view('fleet::booking.bookingShow', compact('booking'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return redirect()->back();
        return view('fleet::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return redirect()->back();
    }
}
