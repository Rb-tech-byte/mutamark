<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Appointment\Entities\Appointment;
use Modules\Appointment\Entities\Schedule;

class DashboardController extends Controller
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
        if (Auth::user()->isAbleTo('appointment dashboard manage')) {
            $userObj          = Auth::user();
            $events = [];
            $currentWorkspace = getActiveWorkSpace();

            $totalAppointments = Appointment::join("schedules", "schedules.appointment_id", "=", "appointments.id")
                ->where("schedules.created_by", "=", $userObj->id)
                ->where('appointments.workspace', '=', $currentWorkspace)
                ->select(
                    'appointments.name as appointment_name',
                    'schedules.name as schedule_name',
                    'schedules.date as date',
                    'schedules.start_time as start_time',
                    'schedules.end_time as end_time',
                    'schedules.status as status'
                )
                ->count();

            $totalPending = Schedule::where('status', '=', 'Pending')->where('workspace', $currentWorkspace)->count();
            $totalArrpvoed = Schedule::where('status', '=', 'Approved')->where('workspace', $currentWorkspace)->count();
            $totalReject = Schedule::where('status', '=', 'Reject')->where('workspace', $currentWorkspace)->count();

            $appointments = Appointment::join("schedules", "schedules.appointment_id", "=", "appointments.id")
                ->where("schedules.created_by", "=", $userObj->id)
                ->where('appointments.workspace', '=', $currentWorkspace)
                ->select(
                    'appointments.id as appointment_id',
                    'schedules.id as schedule_id',
                    'appointments.name as appointment_name',
                    'schedules.name as schedule_name',
                    'schedules.date as date',
                    'schedules.start_time as start_time',
                    'schedules.end_time as end_time',
                    'schedules.status as status'
                )
                ->orderBy('appointments.id', 'desc')->limit(5)->get();

            $arr = [];
            array_push($arr, $totalAppointments, $totalPending, $totalArrpvoed, $totalReject);
            $arrProcessPer = json_encode($arr);

            $appointment_events = \Modules\Appointment\Entities\Schedule::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->where('status', '=', 'Approved');
            if (!empty($request->date)) {
                $date_range = explode(' to ', $request->date);
                $appointment_events->where('date', '>=', $date_range[0]);
                $appointment_events->where('date', '<=', $date_range[1]);
            }
            $appointment_events = $appointment_events->get();
            foreach ($appointment_events as $key => $appointment_event) {
                $data = [
                    'id'    => $appointment_event->id,
                    'title' => $appointment_event->appointment->name,
                    'start' => $appointment_event->date,
                    'end' => $appointment_event->date,
                    'className' => 'event-primary'
                ];

                array_push($events, $data);
            }

            return view('appointment::index', compact('currentWorkspace', 'totalAppointments', 'totalArrpvoed', 'totalReject', 'appointments', 'events', 'totalPending', 'arrProcessPer'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return redirect()->back();
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
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return redirect()->back();
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
