<?php

namespace Modules\Appointment\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Modules\Appointment\Entities\AppointmentCallback;
use Modules\Appointment\Entities\Schedule;
use Modules\Appointment\Events\AppointmentStatus;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('schedule manage')) {
            $schedule = Schedule::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())->with(['creatorName', 'appointment'])->get();
            $callbacks = AppointmentCallback::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            return view('appointment::schedule.index', compact('schedule', 'callbacks'));
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
        if (Auth::user()->isAbleTo('schedule show')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
            $schedule = Schedule::where('created_by', creatorId())
            ->where('workspace', getActiveWorkSpace())->where('appointment_id', $id)->with(['creatorName', 'appointment'])->get();
            $callbacks = AppointmentCallback::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())->where('appointment_id', $id)->get();
            return view('appointment::schedule.index', compact('schedule', 'callbacks'));
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
        if (Auth::user()->isAbleTo('schedule delete')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }

            $schedule = Schedule::find($id);
            if ($schedule->created_by == creatorId() && $schedule->workspace == getActiveWorkSpace()) {
                $schedule->delete();
                return redirect()->route('schedules.index')->with('success', __('Schedule deleted successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function action($id)
    {
        if (Auth::user()->isAbleTo('schedule action')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
            $schedule = Schedule::find($id);
            $users = User::where('created_by', creatorId())
                ->where('workspace_id', getActiveWorkSpace())
                ->emp()->get()->pluck('name', 'id');
            $questions = json_decode($schedule->questions, true);
            return view('appointment::schedule.action', compact('schedule', 'questions', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function changeaction(Request $request)
    {
        if (Auth::user()->isAbleTo('schedule manage')) {
            $schedule = Schedule::find($request->schedule_id);
            $company_settings = getCompanyAllSetting();

            if ($schedule->meeting_type == 'Zoom Meeting' && $request->status != "Reject") {
                if (empty($company_settings['zoom_account_id']) || empty($company_settings['zoom_client_id']) || empty($company_settings['zoom_client_secret'])) {
                    return redirect()->back()->with('error', __('Please first add Zoom meeting credential.'));
                }
            }

            if ($schedule->meeting_type == 'Google Meet' && $request->status != "Reject") {
                if (empty($company_settings['google_meet_json_file']) || empty(check_file($company_settings['google_meet_json_file']))) {
                    return redirect()->back()->with('error', __('Please first add Google meet credential.'));
                }
            }

            // if ($schedule->meeting_type == 'Google Meet') {
            //     if (check_file(company_setting('google_meet_json_file')) && !empty(company_setting('google_meet_json_file'))) {
            //         return redirect()->back()->with('error', __('You have not authorized your google account to Create Google Meeting.'));
            //     }
            // }

            if ($request->status == 'Approved') {
                $schedule->user_id = $request->user_id;
                $schedule->status = $request->status;
                $schedule->save();
            } else {
                $schedule->status = $request->status;
                $schedule->send_feedback = !empty($request->send_feedback) ? $request->send_feedback : '';
                $schedule->save();
            }

            event(new AppointmentStatus($request, $schedule));

            if ($request->status == 'Approved' && (!empty($company_settings['Appointment Status']) && $company_settings['Appointment Status']  == true)) {
                $User     = Schedule::where('id', $schedule->id)
                    ->where('workspace', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'appointment_email' => $User->email,
                    'appointment_status_name' => $User->name,
                    'appointment_status' => $request->status,
                    'appointment_join_url' => !empty($User->join_url) ? $User->join_url : '',
                ];
                try {
                    $resp = EmailTemplate::sendEmailTemplate('Appointment Status', [$User->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('schedules.index')->with('success', __('Schedule status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('success', __('Schedule status successfully updated.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function callbackaction($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $callbacks = AppointmentCallback::find($id);
        return view('appointment::schedule.callback_action', compact('callbacks'));
    }

    public function callbackchangeaction(Request $request)
    {
        if (Auth::user()->isAbleTo('schedule manage')) {
            $callbacks = AppointmentCallback::find($request->callback_id);
            $company_settings = getCompanyAllSetting();
            if ($request->status == 'Approved') {
                $callbacks->user_id = $callbacks->schedule->user_id;
                $callbacks->status = $request->status;
                $callbacks->save();
            } else {
                $callbacks->status = $request->status;
                $callbacks->save();
            }

            if ($request->status == 'Approved' && (!empty($company_settings['Appointment Status']) && $company_settings['Appointment Status']  == true)) {
                $User     = AppointmentCallback::where('id', $callbacks->id)
                    ->where('workspace', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'appointment_email' => $User->schedule->email,
                    'appointment_status_name' => $User->schedule->name,
                    'appointment_status' => $request->status,
                    'appointment_join_url' => $callbacks->join_url,
                ];
                try {
                    $resp = EmailTemplate::sendEmailTemplate('Appointment Status', [$User->schedule->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('schedules.index')->with('success', __('Schedule status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('success', __('Schedule status successfully updated.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function CallbackDestroy($id)
    {
        if (Auth::user()->isAbleTo('schedule delete')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }

            $callbacks = AppointmentCallback::find($id);
            if ($callbacks->created_by == creatorId() && $callbacks->workspace == getActiveWorkSpace()) {
                $callbacks->delete();
                return redirect()->route('schedules.index')->with('success', __('Schedule deleted successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
