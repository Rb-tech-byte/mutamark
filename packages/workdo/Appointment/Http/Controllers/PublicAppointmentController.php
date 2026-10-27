<?php

namespace Modules\Appointment\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Modules\Appointment\Entities\Appointment;
use Modules\Appointment\Entities\AppointmentCallback;
use Modules\Appointment\Entities\Question;
use Modules\Appointment\Entities\Schedule;
use Modules\Appointment\Events\AppointmentCallbackEvent;
use Modules\Appointment\Events\CreateAppointments;

class PublicAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index($slug, $unique_id)
    {
        try {
            $unique_id = Crypt::decrypt($unique_id);
        } catch (\Throwable $th) {
            return redirect()->back();
        }
        $appointment = Schedule::where('unique_id', '=', $unique_id)->first();
        $week_day = Appointment::where('id', $appointment->appointment_id)->first();
        $workspace = WorkSpace::where('slug', $slug)->first();
        if ($appointment) {
            return view('appointment::schedule.show', compact('appointment', 'workspace', 'week_day'));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
        return view('appointment::schedule.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($slug, $id)
    {
        try {
            $slug = $slug;
        } catch (\Throwable $th) {
            return redirect('login');
        }

        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back();
        }
        $workspace = WorkSpace::where('slug', $slug)->first();
        $appointment = Appointment::where('created_by', $workspace->created_by)
            ->where('workspace', $workspace->id)
            ->where('id', $id)->first();

        if ($appointment == null || $appointment->is_enabled == 'off') {
            return view('appointment::appointment.data_not_found', compact('workspace'));
        }

        $available_answer = explode(',', $appointment->question);
        $question = Question::where('created_by', $workspace->created_by)
            ->where('workspace', $workspace->id)->where('is_enabled', 'on')->get();

        $meetings = [];
        if (module_is_active('ZoomMeeting')) {
            $meetings[] = 'ZoomMeeting';
        }
        if (module_is_active('GoogleMeet')) {
            $meetings[] = 'GoogleMeet';
        }

        $meeting_type = explode(',', $appointment->meeting_type);
        $enable_field = explode(',', $appointment->enable_field);

        return view('appointment::appointment.public_appointment', compact('question', 'workspace', 'appointment', 'available_answer', 'meetings', 'meeting_type', 'enable_field'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $slug, $id)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required',
        ];
        
        if ($request->has('phone')) {
            $rules['phone'] = 'required';
        }
        
        if ($request->has('date')) {
            $rules['date'] = 'required|after:yesterday';
        }
        
        if ($request->has('start_time')) {
            $rules['start_time'] = 'required';
        }
        
        if ($request->has('start_time') && $request->has('end_time')) {
            $rules['end_time'] = 'required|after_or_equal:start_time';
        } elseif ($request->has('end_time')) {
            $rules['end_time'] = 'required';
        }
        
        $validator = \Validator::make(
            $request->all(),
            $rules
        );
        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }        

        try {
            $slug = $slug;
        } catch (\Throwable $th) {
            return redirect('login');
        }

        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
        }

        $workspace = WorkSpace::where('slug', $slug)->first();
        $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);

        $schedule = new Schedule();
        $schedule->unique_id = time();
        $schedule->name = $request->name;
        $schedule->email = $request->email;
        $schedule->phone = $request->phone;
        $schedule->date = $request->date;
        $schedule->start_time = $request->start_time;
        $schedule->end_time = $request->end_time;
        $schedule->questions = !empty($request->question) ? json_encode($request->question) : '';
        $schedule->status = 'Pending';
        $schedule->meeting_type = $request->meeting_type;
        $schedule->appointment_id = $id;
        $schedule->workspace = $workspace->id;
        $schedule->created_by = $workspace->created_by;
        $schedule->save();

        // Notification event
        event(new CreateAppointments($request, $schedule));

        if (!empty($company_settings['Appointment Send']) && $company_settings['Appointment Send'] == true) {
            $User     = Schedule::where('id', $schedule->id)
                ->where('workspace', '=', $schedule->workspace)->first();
            $uArr = [
                'appointment_user_name' => $schedule->name,
                'appointment_user_email' => $schedule->email,
                'appointment_unique_id' => $schedule->unique_id,
            ];
            try {
                $resp = EmailTemplate::sendEmailTemplate('Appointment Send', [$schedule->email], $uArr, $schedule->created_by, $schedule->workspace);
            } catch (\Exception $e) {
                $resp['error'] = $e->getMessage();
            }
            return view('appointment::appointment.form_submit', compact('workspace'))->with('success', __('Appointment Created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return view('appointment::appointment.form_submit', compact('workspace'));
        }

        return view('appointment::appointment.form_submit', compact('workspace'));
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

    public function search($slug)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();
        return view('appointment::appointment.search', compact('workspace'));
    }

    public function appointmentSearch($slug, Request $request)
    {
        $validation = [
            'unique_id' => ['required'],
            'email' => ['required'],
        ];

        $validator = \Validator::make(
            $request->all(),
            $validation
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withInput()->with('error', $messages->first());
        }
        $appointment = Schedule::where('unique_id', '=', $request->unique_id)->where('email', '=', $request->email)->first();

        if ($appointment) {
            return redirect()->route('appointment.view', [$slug, Crypt::encrypt($appointment->unique_id)]);
        } else {
            return redirect()->back()->with('info', __('Invalid Appointment Number'));
        }

        return view('appointment.search');
    }

    public function CancelForm($slug, $unique_id, Request $request)
    {
        $appointment = Schedule::where('unique_id', '=', $unique_id)->first();
        if ($appointment) {
            $validation = [
                'cancel_description' => 'required'
            ];
            $validator = \Validator::make(
                $request->all(),
                $validation
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            if ($appointment) {
                $appointment->cancel_description = $request->cancel_description;
                $appointment->status = $request->status;
                $appointment->save();
            }

            return redirect()->back()->with('success', __('Appointment Canceled successfully'));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function Callback(Request $request, $slug, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back();
        }
        $schedule = Schedule::find($id);
        $workspace = WorkSpace::where('slug', $slug)->first();
        $appointment_callback = new AppointmentCallback();
        $appointment_callback->schedule_id = $id;
        $appointment_callback->unique_id = $schedule->unique_id;
        $appointment_callback->user_id = $schedule->user_id;
        $appointment_callback->appointment_id = $schedule->appointment_id;
        $appointment_callback->reason = $request->callback_description;
        $appointment_callback->date = $request->date;
        $appointment_callback->start_time = $request->start_time;
        $appointment_callback->end_time = $request->end_time;
        $appointment_callback->workspace = $workspace->id;
        $appointment_callback->created_by = $workspace->created_by;
        $appointment_callback->save();

        event(new AppointmentCallbackEvent($request, $appointment_callback));

        $company_settings = getCompanyAllSetting($appointment_callback->created_by, $appointment_callback->workspace);

        if (!empty($company_settings['Appointment Send']) && $company_settings['Appointment Send']  == true) {
            $User     = AppointmentCallback::where('schedule_id', $schedule->id)
                ->where('workspace', '=', $appointment_callback->workspace)->first();
            $uArr = [
                'appointment_user_name' => $schedule->name,
                'appointment_user_email' => $schedule->email,
                'appointment_unique_id' => $appointment_callback->unique_id,
            ];
            try {
                $resp = EmailTemplate::sendEmailTemplate('Appointment Send', [$schedule->email], $uArr, $appointment_callback->created_by, $appointment_callback->workspace);
            } catch (\Exception $e) {
                $resp['error'] = $e->getMessage();
            }
            return redirect()->back()->with('success', __('Your Appointment Callback Send successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('success', __('Your Appointment Callback Send successfully.'));
        }

        return redirect()->back()->with('success', __('Your Appointment Callback Send successfully'));
    }
}
