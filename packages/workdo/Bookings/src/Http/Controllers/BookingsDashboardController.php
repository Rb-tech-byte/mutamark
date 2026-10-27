<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\WorkSpace;
use Workdo\Bookings\Entities\BookingsAppointment;


class BookingsDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }
    public function index(Request $request)
    {
        $beauty_book     = [];
        $bookingsappointment   = BookingsAppointment::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace());
        $workspace       = WorkSpace::where('id', getActiveWorkSpace())->first();

        if (!empty($request->date)) {
            $date_range = explode(' to ', $request->date);
            $beautybooking->where('start_date', '>=', $date_range[0]);
            $beautybooking->where('end_date', '<=', $date_range[1]);
        }
        $bookingsappointments = $bookingsappointment->get();

        foreach ($bookingsappointments as $key => $bookingsappointment) {
            $formattedAppointmentNumber = \Workdo\Bookings\Entities\BookingsAppointment::appointmentNumberFormat($bookingsappointment->appointment_id);

            $data = [
                'title' => $formattedAppointmentNumber,
                'start' => $bookingsappointment->date,
                'end' => $bookingsappointment->date,
                'className' => 'event-danger'
            ];
            array_push($beauty_book, $data);
        }

        if (\Auth::user()->type == 'company') {
            $current_month_booking = BookingsAppointment::whereMonth('date', date('m'))
                ->whereYear('date', date('Y'))
                ->where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->OrderBy('date', 'ASC')
                ->get();

        } else {
            $current_month_booking = BookingsAppointment::whereMonth('date', date('m'))
                ->whereYear('date', date('Y'))
                ->where('workspace', getActiveWorkSpace())
                ->OrderBy('date', 'ASC')
                ->get();
        }
        $format        = 'Y-m-d';
        $m             = date("m");
        $de            = date("d");
        $y             = date("Y");

        $arrCount['totalAppointment']    = BookingsAppointment::where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace())->count();
        $arryTemp = [];
        for ($i = 0; $i <= 7 - 1; $i++) {
            $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
            $arryTemp['appointment_date'][]    = __(date('d-M', strtotime($date)));
            $arryTemp['appointment'][] = BookingsAppointment::whereDate('date', $date)->where('workspace', '=', getActiveWorkSpace())->count();
        }
        $chartcall = $arryTemp;

        $chartcall['appointment_date'] = $arryTemp['appointment_date'];
        $chartcall['appointment']    = $arryTemp['appointment'];

        $data['pendingappointment'] = BookingsAppointment::where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace())->where('stage_id','=','0')->count();
        $data['cancelledappointment'] = BookingsAppointment::where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace())->where('stage_id','=','3')->count();
        $data['completedappointment'] = BookingsAppointment::where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace())->where('stage_id','=','4')->count();


        return view('bookings::dashboard.index',compact('beauty_book','current_month_booking','chartcall','workspace','data'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('bookings::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('bookings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('bookings::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
