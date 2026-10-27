<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Hrm\Entities\Branch;
use Modules\Recruitment\Entities\CustomQuestion;
use Modules\Recruitment\Entities\InterviewSchedule;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\JobApplication;
use Modules\Recruitment\Entities\JobApplicationNote;
use Modules\Recruitment\Entities\JobCategory;
use Modules\Recruitment\Entities\JobOnBoard;
use Modules\Recruitment\Entities\JobStage;
use Modules\Recruitment\Events\CreateJob;
use Modules\Recruitment\Events\CreateJobApplication;
use Modules\Recruitment\Events\DestroyJob;
use Modules\Recruitment\Events\UpdateJob;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('job manage')) {
            $jobs = Job::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->with(['branches', 'createdBy'])->get();

            $data['total']     = Job::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->count();
            $data['active']    = Job::where('status', 'active')->where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->count();
            $data['in_active'] = Job::where('status', 'in_active')->where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->count();
            return view('recruitment::job.index', compact('jobs', 'data'));
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
        if (Auth::user()->isAbleTo('job create')) {
            $categories = JobCategory::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('title', 'id');

            $branches = Branch::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            $status = Job::$status;

            $customQuestion = CustomQuestion::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            $users = User::where('created_by', '=', creatorId())->where('type', '=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            if (count($users) != 0) {

                $users->prepend(__('Select Client'), '');
            }

            $job_type        = Job::$job_type;

            $recruitment_type = [];

            if (module_is_active('Hrm')) {
                $recruitment_type = [
                    '' => __('Select Recruitment Type'),
                    'internal' => __('Internal'),
                    'client' => __('Client'),
                ];
            } else {
                $recruitment_type = [
                    '' => __('Select Recruitment Type'),
                    'client' => __('Client'),
                ];
            }

            return view('recruitment::job.create', compact('categories', 'status', 'branches', 'customQuestion', 'users', 'job_type', 'recruitment_type'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('job create')) {

            $rules = [
                'title' => 'required',
                'recruitment_type' => 'required',
                'location' => 'required',
                'category' => 'required',
                'job_type' => 'required',
                'salary_from' => 'required',
                'salary_to' => 'required',
                'skill' => 'required',
                'position' => 'required|min:0',
                'start_date' => 'required|after:yesterday',
                'end_date' => 'required|after_or_equal:start_date',
                'description' => 'required',
                'requirement' => 'required',
                'custom_question.*' => 'required',
            ];

            if (module_is_active('Hrm') && $request->has('branch')) {
                $rules['branch'] = 'required';
            }

            if (is_array($request->visibility) && in_array('terms', $request->visibility)) {
                $rules['terms_and_conditions'] = 'required';
            }

            $validator = \Validator::make(
                $request->all(),
                $rules
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job                       = new Job();
            $job->title                = $request->title;
            $job->recruitment_type     = $request->recruitment_type;
            $job->branch               = !empty($request->branch) ? $request->branch : 0;
            $job->location             = !empty($request->location) ? $request->location : '';
            $job->category             = $request->category;
            $job->user_id              = $request->user_id;
            $job->skill                = $request->skill;
            $job->position             = $request->position;
            $job->status               = $request->status;
            $job->job_type             = $request->job_type;
            $job->salary_from          = $request->salary_from;
            $job->salary_to            = $request->salary_to;
            $job->start_date           = $request->start_date;
            $job->end_date             = $request->end_date;
            $job->description          = $request->description;
            $job->requirement          = $request->requirement;
            $job->terms_and_conditions = !empty($request->terms_and_conditions) ? $request->terms_and_conditions : '';
            $job->code                 = uniqid();
            $job->applicant            = !empty($request->applicant) ? implode(',', $request->applicant) : '';
            $job->visibility           = !empty($request->visibility) ? implode(',', $request->visibility) : '';
            $job->custom_question      = !empty($request->custom_question) ? implode(',', $request->custom_question) : '';
            $job->workspace            = getActiveWorkSpace();
            $job->created_by           = creatorId();
            $job->save();

            event(new CreateJob($request, $job));

            return redirect()->route('job.index')->with('success', __('Job  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Job $job)
    {
        if (Auth::user()->isAbleTo('job show')) {
            $status          = Job::$status;
            $job->applicant  = !empty($job->applicant) ? explode(',', $job->applicant) : '';
            $job->visibility = !empty($job->visibility) ? explode(',', $job->visibility) : '';
            $job->skill      = !empty($job->skill) ? explode(',', $job->skill) : '';

            return view('recruitment::job.show', compact('status', 'job'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Job $job)
    {
        if (Auth::user()->isAbleTo('job edit')) {
            $categories = JobCategory::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('title', 'id');

            $branches = Branch::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            $status = Job::$status;

            $job->applicant       = explode(',', $job->applicant);
            $job->visibility      = explode(',', $job->visibility);
            $job->custom_question = explode(',', $job->custom_question);

            $customQuestion = CustomQuestion::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            $users = User::where('created_by', '=', creatorId())->where('type', '=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            if (count($users) != 0) {

                $users->prepend(__('Select Client'), '');
            }

            $job_type        = Job::$job_type;

            $recruitment_type = [];

            if (module_is_active('Hrm')) {
                $recruitment_type = [
                    '' => __('Select Recruitment Type'),
                    'internal' => __('Internal'),
                    'client' => __('Client'),
                ];
            } else {
                $recruitment_type = [
                    '' => __('Select Recruitment Type'),
                    'client' => __('Client'),
                ];
            }

            return view('recruitment::job.edit', compact('categories', 'status', 'branches', 'job', 'customQuestion', 'users', 'job_type', 'recruitment_type'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Job $job)
    {
        if (Auth::user()->isAbleTo('job edit')) {

            $rules = [
                'title' => 'required',
                'recruitment_type' => 'required',
                'location' => 'required',
                'category' => 'required',
                'job_type' => 'required',
                'salary_from' => 'required',
                'salary_to' => 'required',
                'skill' => 'required',
                'position' => 'required|min:0',
                'start_date' => 'required|after:yesterday',
                'end_date' => 'required|after_or_equal:start_date',
                'description' => 'required',
                'requirement' => 'required',
                'custom_question.*' => 'required',
            ];

            if (module_is_active('Hrm') && $request->has('branch')) {
                $rules['branch'] = 'required';
            }

            if (is_array($request->visibility) && in_array('terms', $request->visibility)) {
                $rules['terms_and_conditions'] = 'required';
            }

            $validator = \Validator::make(
                $request->all(),
                $rules
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job->title                = $request->title;
            $job->recruitment_type     = $request->recruitment_type;
            $job->branch               = !empty($request->branch) ? $request->branch : 0;
            $job->location             = !empty($request->location) ? $request->location : '';
            $job->category             = $request->category;
            $job->user_id              = $request->user_id;
            $job->skill                = $request->skill;
            $job->position             = $request->position;
            $job->status               = $request->status;
            $job->job_type             = $request->job_type;
            $job->salary_from          = $request->salary_from;
            $job->salary_to            = $request->salary_to;
            $job->start_date           = $request->start_date;
            $job->end_date             = $request->end_date;
            $job->description          = $request->description;
            $job->requirement          = $request->requirement;
            $job->terms_and_conditions = !empty($request->terms_and_conditions) ? $request->terms_and_conditions : '';
            $job->applicant            = !empty($request->applicant) ? implode(',', $request->applicant) : '';
            $job->visibility           = !empty($request->visibility) ? implode(',', $request->visibility) : '';
            $job->custom_question      = !empty($request->custom_question) ? implode(',', $request->custom_question) : '';
            $job->save();
            event(new UpdateJob($request, $job));
            return redirect()->route('job.index')->with('success', __('Job  successfully updated.'));
        } else {
            return redirect()->route('job.index')->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Job $job)
    {
        if (Auth::user()->isAbleTo('job delete')) {
            $application = JobApplication::where('job', $job->id)->get()->pluck('id');
            event(new DestroyJob($job));
            JobOnBoard::whereIn('application', $application)->delete();
            InterviewSchedule::whereIn('candidate', $application)->delete();
            JobApplicationNote::whereIn('application_id', $application)->delete();
            JobApplication::where('job', $job->id)->delete();

            $job->delete();

            return redirect()->route('job.index')->with('success', __('Job  successfully deleted.'));
        } else {
            return redirect()->route('job.index')->with('error', __('Permission denied.'));
        }
    }

    public function career($slug = null, $lang = null)
    {
        if (!empty($slug)) {
            try {
                $workspace = WorkSpace::where('slug', $slug)->first();
                $workspace_id = $workspace->id;
            } catch (\Throwable $th) {
                return redirect()->back();
            }
        } else {
            try {
                $workspace = getActiveWorkSpace();
                $workspace_id = $workspace;
                $workspace = WorkSpace::where('id', $workspace)->first();
                $slug = $workspace->slug;
            } catch (\Throwable $th) {
                return redirect()->back();
            }
        }
        $company_id = $workspace->created_by;

        try {
            $slug = $slug;
        } catch (\Throwable $th) {
            return redirect('login');
        }
        if ($lang == null) {
            $lang = 'en';
        }

        $jobs = Job::where('created_by', $company_id)->where('status', 'active')->where('workspace', $workspace_id)->with('branches')->get();
        \Session::put('lang', $lang);

        \App::setLocale($lang);

        $languages                          = languages();

        $currantLang = \Session::get('lang');
        if (empty($currantLang)) {
            $user        = User::find($company_id);
            $currantLang = !empty($user) && !empty($user->lang) ? $user->lang : 'en';
        }

        return view('recruitment::job.career', compact('jobs', 'languages', 'currantLang', 'company_id', 'workspace_id', 'slug'));
    }

    public function jobRequirement($code, $lang)
    {
        $job = Job::where('code', $code)->first();
        if($job)
        {
            if ($job->status == 'in_active') {
                return redirect()->back()->with('error', __('This Job is not Active.'));
            }

            \Session::put('lang', $lang);

            \App::setLocale($lang);


            $languages = languages();

            $currantLang = \Session::get('lang');
            if (empty($currantLang)) {
                $currantLang = !empty($job->createdBy) ? $job->createdBy->lang : 'en';
            }

            $company_id = $job->created_by;
            $workspace_id = $job->workspace;
            return view('recruitment::job.requirement', compact('job', 'languages', 'currantLang', 'company_id', 'workspace_id'));
        }
        else{
            return redirect()->back()->with('error', __('This Job is not Found.'));
        }
    }

    public function jobApply($code, $lang)
    {
        \Session::put('lang', $lang);

        \App::setLocale($lang);

        $job                                = Job::where('code', $code)->first();

        $que = !empty($job->custom_question) ? explode(",", $job->custom_question) : [];

        $questions = CustomQuestion::wherein('id', $que)->get();

        $languages = languages();

        $currantLang = \Session::get('lang');
        if (empty($currantLang)) {
            $currantLang = !empty($job->createdBy) ? $job->createdBy->lang : 'en';
        }

        $company_id = $job->created_by;
        $workspace_id = $job->workspace;
        return view('recruitment::job.apply', compact('job', 'questions', 'languages', 'currantLang', 'company_id', 'workspace_id'));
    }

    public function TermsAndCondition($code, $lang)
    {
        $job = Job::where('code', $code)->first();
        if($job)
        {
            if ($job->status == 'in_active') {
                return redirect()->back()->with('error', __('This Job is not Active.'));
            }

            \Session::put('lang', $lang);

            \App::setLocale($lang);

            $languages = languages();

            $currantLang = \Session::get('lang');
            if (empty($currantLang)) {
                $currantLang = !empty($job->createdBy) ? $job->createdBy->lang : 'en';
            }

            $company_id = $job->created_by;
            $workspace_id = $job->workspace;
            return view('recruitment::job.terms', compact('job', 'languages', 'currantLang', 'company_id', 'workspace_id'));
        }
        else{
            return redirect()->back()->with('error', __('This Job is not Found.'));
        }
    }

    public function jobApplyData(Request $request, $code)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ];
        if (isset($request->terms_condition_check) && empty($request->terms_condition_check)) {
            $rules['terms_condition_check'] = [
                'required',
            ];
        }

        $validator = \Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $job = Job::where('code', $code)->first();

        if (!empty($request->profile)) {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = upload_file($request, 'profile', $fileNameToStore, 'JobApplication');
            if ($uplaod['flag'] == 1) {
                $url = $uplaod['url'];
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }

        if (!empty($request->resume)) {
            $filenameWithExt1 = $request->file('resume')->getClientOriginalName();
            $filename1        = pathinfo($filenameWithExt1, PATHINFO_FILENAME);
            $extension1       = $request->file('resume')->getClientOriginalExtension();
            $fileNameToStore1 = $filename1 . '_' . time() . '.' . $extension1;

            $uplaod = upload_file($request, 'resume', $fileNameToStore1, 'JobApplication');
            if ($uplaod['flag'] == 1) {
                $url1 = $uplaod['url'];
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }
        $job_user = User::find($job->created_by);
        $stage = JobStage::where('created_by', $job_user->id)->where('order', \DB::raw("(select min(`order`) from job_stages)"))->first();

        $jobApplication                  = new JobApplication();
        $jobApplication->job             = $job->id;
        $jobApplication->name            = $request->name;
        $jobApplication->email           = $request->email;
        $jobApplication->phone           = $request->phone;
        $jobApplication->profile         = !empty($request->profile) ? $url : '';
        $jobApplication->resume          = !empty($request->resume) ? $url1 : '';
        $jobApplication->cover_letter    = $request->cover_letter;
        $jobApplication->dob             = $request->dob;
        $jobApplication->gender          = $request->gender;
        $jobApplication->country         = $request->country;
        $jobApplication->state           = $request->state;
        $jobApplication->city            = $request->city;
        $jobApplication->stage           = !empty($stage) ? $stage->id : 1;
        $jobApplication->custom_question = json_encode($request->question);
        $jobApplication->workspace      = getActiveWorkSpace($job->created_by);
        $jobApplication->created_by      = $job->created_by;
        $jobApplication->save();

        event(new CreateJobApplication($request, $jobApplication));

        return redirect()->back()->with('success', __('Job application successfully send.'));
    }

    public function grid()
    {
        if (Auth::user()->isAbleTo('job manage')) {
            $jobs = Job::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->with('branches')->get();

            $data['total']     = Job::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->count();
            $data['active']    = Job::where('status', 'active')->where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->count();
            $data['in_active'] = Job::where('status', 'in_active')->where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->count();
            return view('recruitment::job.grid', compact('jobs', 'data'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }
}
