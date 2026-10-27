<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\InterviewSchedule;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\JobApplication;
use Modules\Recruitment\Entities\JobStage;

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

    public function index()
    {
        if (Auth::user()->isAbleTo('recruitment dashboard manage')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $workspace       = WorkSpace::where('id', $getActiveWorkSpace)->first();
            $transdate = date('Y-m-d', time());

            $arrCount      = [];
            $calenderTasks = [];

            $arrCount['job_published']    = Job::where('status', '=', 'active')
                ->where('created_by', '=', $creatorId)
                ->where('workspace', '=', $getActiveWorkSpace)->count();
            $arrCount['job_expired']      = Job::where('status', '=', 'in_active')
                ->orWhere('end_date', '<', now())
                ->where('created_by', '=', $creatorId)
                ->where('workspace', '=', $getActiveWorkSpace)->count();
            $arrCount['job_candidate']    = JobApplication::where('created_by', '=', $creatorId)->where('is_employee', '!=', 1)
                ->where('workspace', '=', $getActiveWorkSpace)->count();

            $interview_schedule = InterviewSchedule::where('created_by', $creatorId)->where('workspace', $getActiveWorkSpace)->get();
            foreach ($interview_schedule as $schedule) {
                if (!empty($schedule)) {
                    $calenderTasks[] = [
                        'title' => !empty($schedule->applications) ? (!empty($schedule->applications->jobs) ? $schedule->applications->jobs->title : '') : '',
                        'start' => $schedule->date,
                        'className' => 'event-danger',
                    ];
                } else {
                    $calenderTasks[] = [];
                }
            }

            $jobs = Job::where('created_by', '=', $creatorId)->where('workspace', '=', $getActiveWorkSpace)->take(5)->get();

            $deal_stage = JobStage::where('created_by', $creatorId)->where('workspace', '=', $getActiveWorkSpace)->orderBy('order', 'ASC')->get();

            $dealStageName = [];
            $dealStageData = [];
            foreach ($deal_stage as $deal_stage_data) {
                $deal_stage = JobApplication::where('created_by', $creatorId)->where('is_archive', '!=', 1)->where('workspace', '=', $getActiveWorkSpace)->where('stage', $deal_stage_data->id)->orderBy('order', 'ASC')->count();
                $dealStageName[] = $deal_stage_data->title;
                $dealStageData[] = $deal_stage;
            }

            return view('recruitment::index', compact('arrCount', 'workspace', 'calenderTasks', 'transdate', 'jobs', 'dealStageData', 'dealStageName'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return redirect()->back();
        return view('recruitment::create');
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
        return view('recruitment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return redirect()->back();
        return view('recruitment::edit');
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
