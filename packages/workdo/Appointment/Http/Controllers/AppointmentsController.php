<?php

namespace Modules\Appointment\Http\Controllers;

use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Modules\Appointment\Entities\Appointment;
use Modules\Appointment\Entities\Question;
use Modules\Appointment\Entities\Schedule;
use Modules\Appointment\Events\CreateAppointment;
use Modules\Appointment\Events\DestroyAppointment;
use Modules\Appointment\Events\UpdateAppointment;

class AppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('appointments manage')) {
            $appointment = Appointment::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())->get();
            $workspace       = WorkSpace::where('id', getActiveWorkSpace())->first();
            return view('appointment::appointment.index', compact('appointment', 'workspace'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('appointments create')) {
            $question = Question::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->where('is_enabled', 'on')->get();
            $appointment = Appointment::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())->first();
            $appointment_type = Appointment::$appointment_type;
            $week_days = Appointment::$week_day;

            $meetings = [];
            if (module_is_active('ZoomMeeting')) {
                $meetings[] = 'ZoomMeeting';
            }
            if (module_is_active('GoogleMeet')) {
                $meetings[] = 'GoogleMeet';
            }

            return view('appointment::appointment.create', compact('question', 'appointment', 'appointment_type', 'week_days', 'meetings'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('appointments create')) {
            $validation = [
                'appointment_name' => 'required',
                'appointment_type' => 'required',
                // 'date'             => 'required|after:yesterday',
                'week_day'         => 'required',
                // 'start_time'       => 'required',
                // 'end_time'         => 'required|after_or_equal:start_time',
                'is_enabled'       => 'required',
            ];
            $validator = \Validator::make(
                $request->all(),
                $validation
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $post = [
                'name'             => $request->appointment_name,
                'appointment_type' => $request->appointment_type,
                // 'date'             => $request->date,
                'week_day'         => !empty($request->week_day) ? implode(', ', $request->week_day) : '',
                // 'start_time'       => $request->start_time,
                // 'end_time'         => $request->end_time,
                'question'         => !empty($request->question_id) ? implode(',', $request->question_id) : '',
                'meeting_type'     => !empty($request->meeting_type) ? implode(',', $request->meeting_type) : '',
                'enable_field'     => !empty($request->enable_field) ? implode(',', $request->enable_field) : '',
                'is_enabled'       => $request->is_enabled,
                'workspace'        => getActiveWorkSpace(),
                'created_by'       => creatorId(),
            ];
            Appointment::create($post);

            event(new CreateAppointment($request, $post));

            return redirect()->route('appointments.index')->with('success',  __('Appointment created successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (Auth::user()->isAbleTo('appointments show')) {
            $appointment = Appointment::find($id);
            $schedule = Schedule::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->where('appointment_id', $id)->get();
            return view('appointment::appointment.show', compact('appointment', 'schedule'));
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
        if (Auth::user()->isAbleTo('appointments edit')) {
            $appointment = Appointment::find($id);
            if ($appointment->created_by == creatorId() && $appointment->workspace == getActiveWorkSpace()) {
                $question = Question::where('created_by', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->where('is_enabled', 'on')->get();
                $week_days = Appointment::$week_day;
                $appointment_type = Appointment::$appointment_type;
                $questions = explode(',', $appointment->question);

                $meetings = [];
                if (module_is_active('ZoomMeeting')) {
                    $meetings[] = 'ZoomMeeting';
                }
                if (module_is_active('GoogleMeet')) {
                    $meetings[] = 'GoogleMeet';
                }

                $meeting_type = explode(',', $appointment->meeting_type);
                $enable_field = explode(',', $appointment->enable_field);

                return view('appointment::appointment.edit', compact('appointment', 'question', 'week_days', 'questions', 'appointment_type', 'meetings', 'meeting_type', 'enable_field'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Appointment $appointment)
    {
        if (Auth::user()->isAbleTo('appointments edit')) {
            if ($appointment->created_by == creatorId() && $appointment->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        // 'date' => 'required|after:yesterday',
                        'appointment_type' => 'required',
                        'week_day' => 'required',
                        // 'start_time' => 'required',
                        // 'end_time' => 'required|after_or_equal:start_time',
                        'is_enabled' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('appointments.index')->with('error', $messages->first());
                }

                $appointment->name = $request->name;
                $appointment->question = !empty($request->question_id) ? implode(',', $request->question_id) : '';
                $appointment->appointment_type = $request->appointment_type;
                // $appointment->date = $request->date;
                $appointment->week_day = implode(', ', $request->week_day);
                // $appointment->start_time = $request->start_time;
                // $appointment->end_time = $request->end_time;
                $appointment->meeting_type = !empty($request->meeting_type) ? implode(',', $request->meeting_type) : '';
                $appointment->enable_field = !empty($request->enable_field) ? implode(',', $request->enable_field) : '';
                $appointment->is_enabled = $request->is_enabled;
                $appointment->save();

                event(new UpdateAppointment($request, $appointment));

                return redirect()->route('appointments.index')->with('success', __('Appointment updated successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('appointments delete')) {
            $appointment = Appointment::find($id);
            if ($appointment->created_by == creatorId() && $appointment->workspace == getActiveWorkSpace()) {
                $schedule = Schedule::where('created_by', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->where('appointment_id', $id);

                event(new DestroyAppointment($appointment));

                $appointment->delete();
                $schedule->delete();
                return redirect()->route('appointments.index')->with('success', __('Appointment deleted successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function calender(Request $request)
    {
        if (Auth::user()->isAbleTo('appointments manage')) {
            $schedule = Schedule::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace());
            $today_date = date('m');
            $current_month_event = Schedule::select('appointment_id', 'date', 'created_at')
                ->where('workspace', getActiveWorkSpace())
                ->whereRaw('MONTH(date) = ? AND MONTH(date) = ?', [date('m'), date('m')])->get();

            if (!empty($request->date)) {
                $schedule->where('date', '>=', $request->date);
            }
            if (!empty($request->date)) {
                $schedule->where('date', '<=', $request->date);
            }
            $schedules = $schedule->get();

            $arrSchedule = [];

            foreach ($schedules as $schedule) {
                $arr['id'] = $schedule['id'];
                // $arr['title'] = $schedule['name'];
                $arr['title'] = $schedule->appointment->name;
                $arr['email'] = $schedule['email'];
                $arr['phone'] = $schedule['phone'];
                $arr['start'] = $schedule['date'];
                $arr['end'] = $schedule['date'];
                $arr['start_time'] = $schedule['start_time'];
                $arr['end_time'] = $schedule['end_time'];
                $arr['appointment_id'] = $schedule['appointment_id'];
                $arr['status'] = $schedule['status'];
                if ($schedule['status'] == 'Pending') {
                    $arr['className'] = 'event-warning schedule-show';
                } elseif ($schedule['status'] == 'Approved') {
                    $arr['className'] = 'event-success schedule-show';
                } elseif($schedule['status'] == 'Complete') {
                    $arr['className'] = 'event-info schedule-show';
                }else {
                    $arr['className'] = 'event-danger schedule-show';
                }
                $arr['url'] = route('appointments.scheduleshow', \Crypt::encrypt($schedule['id']));
                $arrSchedule[]    = $arr;
            }
            $arrSchedule =  json_encode($arrSchedule);
            return view('appointment::appointment.calender', compact('arrSchedule', 'current_month_event'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function scheduleshow($id)
    {
        if (Auth::user()->isAbleTo('schedule show')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
            $schedule = Schedule::find($id);
            return view('appointment::appointment.scheduleshow', compact('schedule'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
