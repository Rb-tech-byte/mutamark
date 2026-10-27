<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Modules\Recruitment\Entities\JobAward;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Entities\JobExperience;
use Modules\Recruitment\Entities\JobExperienceCandidate;
use Modules\Recruitment\Entities\JobProject;
use Modules\Recruitment\Entities\JobQualification;
use Modules\Recruitment\Entities\JobSkill;
use Modules\Recruitment\Events\CreateJobCandidate;
use Modules\Recruitment\Events\DestroyJobCandidate;
use Modules\Recruitment\Events\UpdateJobCandidate;

class JobCandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('job candidate manage')) {

            $job_candidates_query = JobCandidate::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace());

            if (isset($request->gender) && !empty($request->gender)) {
                $job_candidates_query->where('gender', $request->gender);
            }

            if (isset($request->country) && !empty($request->country)) {
                $job_candidates_query->where('country', $request->country);
            }

            if (isset($request->state) && !empty($request->state)) {
                $job_candidates_query->where('state', $request->state);
            }

            $job_candidates = $job_candidates_query->get();

            $job_candidate_country = JobCandidate::distinct()->pluck('country', 'country');
            $job_candidate_country->prepend('All', '');

            $job_candidate_state = JobCandidate::distinct()->pluck('state', 'state');
            $job_candidate_state->prepend('All', '');

            $filter = [
                'gender' => isset($request->gender) ? $request->gender : '',
                'country' => isset($request->country) ? $request->country : '',
                'state' => isset($request->state) ? $request->state : '',
            ];

            return view('recruitment::jobcandidate.index', compact('job_candidates', 'filter', 'job_candidate_country', 'job_candidate_state'));
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
        if (Auth::user()->isAbleTo('job candidate create')) {

            return view('recruitment::jobcandidate.create');
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
        if (Auth::user()->isAbleTo('job candidate create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required',
                    'dob' => 'before:' . date('Y-m-d'),
                    'phone' => 'required',
                    'gender' => 'required',
                    'address' => 'required',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

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

                $filenameWithExt = $request->file('resume')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('resume')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'resume', $fileNameToStore, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    $url1 = $uplaod['url'];
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            $job_candidate              = new JobCandidate();
            $job_candidate->name        = $request->name;
            $job_candidate->email       = $request->email;
            $job_candidate->phone       = $request->phone;
            $job_candidate->dob         = $request->dob;
            $job_candidate->gender      = $request->gender;
            $job_candidate->address     = $request->address;
            $job_candidate->country     = $request->country;
            $job_candidate->state       = $request->state;
            $job_candidate->city        = $request->city;
            $job_candidate->description = $request->description;
            $job_candidate->profile     = !empty($request->profile) ? $url : '';
            $job_candidate->resume      = !empty($request->resume) ? $url1 : '';
            $job_candidate->workspace   = getActiveWorkSpace();
            $job_candidate->created_by  = creatorId();
            $job_candidate->save();

            event(new CreateJobCandidate($request,  $job_candidate));

            return redirect()->route('job-candidates.edit', Crypt::encrypt($job_candidate->id))->with('success', __('Job Candidate successfully created'))->with('experience-tab', true);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('recruitment::jobcandidate.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Page Not Found.'));
        }
        if (Auth::user()->isAbleTo('job candidate edit')) {
            $job_candidates = JobCandidate::find($id);
            $job_projects = JobProject::where('candidate_id', $job_candidates->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $job_qualifications = JobQualification::where('candidate_id', $job_candidates->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $job_awards = JobAward::where('candidate_id', $job_candidates->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $job_experience_candidates = JobExperienceCandidate::where('candidate_id', $job_candidates->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $job_skills = JobSkill::where('candidate_id', $job_candidates->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $job_experiences = JobExperience::where('candidate_id', $job_candidates->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('recruitment::jobcandidate.edit', compact('job_projects', 'job_qualifications', 'job_awards', 'job_experience_candidates', 'job_skills', 'job_experiences', 'job_candidates'));
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
    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('job candidate edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required',
                    'phone' => 'required',
                    'dob' => 'before:' . date('Y-m-d'),
                    'gender' => 'required',
                    'address' => 'required',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $job_candidate = JobCandidate::find($id);

            if (!empty($request->profile)) {
                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'profile', $fileNameToStore, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    if (!empty($job_candidate->profile)) {
                        delete_file($job_candidate->profile);
                    }
                    $url = $uplaod['url'];
                    $job_candidate->profile = $url;
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            if (!empty($request->resume)) {
                $filenameWithExt = $request->file('resume')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('resume')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'resume', $fileNameToStore, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    if (!empty($job_candidate->resume)) {
                        delete_file($job_candidate->resume);
                    }
                    $url1 = $uplaod['url'];
                    $job_candidate->resume = $url1;
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            $job_candidate->name        = $request->name;
            $job_candidate->email       = $request->email;
            $job_candidate->phone       = $request->phone;
            $job_candidate->dob         = $request->dob;
            $job_candidate->gender      = $request->gender;
            $job_candidate->address     = $request->address;
            $job_candidate->country     = $request->country;
            $job_candidate->state       = $request->state;
            $job_candidate->city        = $request->city;
            $job_candidate->description = $request->description;
            $job_candidate->workspace   = getActiveWorkSpace();
            $job_candidate->created_by  = creatorId();
            $job_candidate->save();

            event(new UpdateJobCandidate($request, $job_candidate));

            return redirect()->back()->with('success', __('Job Candidate successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('job candidate delete')) {
            $currentWorkspace = getActiveWorkSpace();
            $job_candidate = JobCandidate::find($id);

            if ($job_candidate->created_by == creatorId() && $job_candidate->workspace == $currentWorkspace) {

                event(new DestroyJobCandidate($job_candidate));

                if (!empty($job_candidate->profile)) {
                    delete_file($job_candidate->profile);
                }

                if (!empty($job_candidate->resume)) {
                    delete_file($job_candidate->resume);
                }

                $job_candidate->delete();
                return redirect()->back()->with('success', __('Job Candidate successfully deleted.'));
            } else {
                return redirect()->back()->with('error', 'Permission denied.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewJob($template, $color)
    {
        $objUser = \Auth::user();
        $job_candidates = new JobCandidate();
        $job_experiences = [];
        $job_projects = [];
        $job_qualifications = [];
        $job_awards = [];
        $job_experience_candidates = [];
        $job_skills = [];

        $job_candidate = new \stdClass();
        $job_candidate->name = '<Name>';
        $job_candidate->dob = '<Date Of Birth>';
        $job_candidate->country = '<National>';
        $job_candidate->address = '<Address>';
        $job_candidate->country = '<Country>';
        $job_candidate->state = '<State>';
        $job_candidate->city = '<City>';
        $job_candidate->phone = '<Phone>';
        $job_candidate->website = '<Website>';
        $job_candidate->summary = 'Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat libero sto elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit dolor ipsums dolor sit amet consectetur ipsum dolor amet. Consectetur adipiscing elit. amet. Consetetur adipiscing elit. Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat libero sto elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit dolor dolor amet. Lorem ipsum dolor sit amet. Consectetur adipiscing elit.';
        $job_candidate->work_experience_title = 'SENIOR GRAPHIC DESIGNER';
        $job_candidate->work_experience_date = 'June 2020- March 2024';
        $job_candidate->work_experience_description = '<p><span style="font-family: Raleway, sans-serif;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputate libero justo elit dolor Vivamus adipiscng elit vivamus vulputat libero justo. Elit dolor ipsum dolor sit amet. Consectetur ipsum dolor amet.</span></p><ul><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">libero justo elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li></ul>';
        $job_candidate->project_experience_title = 'SENIOR GRAPHIC DESIGNER';
        $job_candidate->project_experience_date = 'June 2020- March 2024';
        $job_candidate->project_experience_description = '<p><span style="font-family: Raleway, sans-serif;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputate libero justo elit dolor Vivamus adipiscng elit vivamus vulputat libero justo. Elit dolor ipsum dolor sit amet. Consectetur ipsum dolor amet.</span></p><ul><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">libero justo elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li></ul>';
        $job_candidate->qualification_experience_title = 'SENIOR GRAPHIC DESIGNER';
        $job_candidate->qualification_experience_date = 'June 2020- March 2024';
        $job_candidate->qualification_experience_description = '<p><span style="font-family: Raleway, sans-serif;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputate libero justo elit dolor Vivamus adipiscng elit vivamus vulputat libero justo. Elit dolor ipsum dolor sit amet. Consectetur ipsum dolor amet.</span></p><ul><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">libero justo elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li></ul>';
        $job_candidate->award_description = '<ul><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">libero justo elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li></ul><ul><ul style="margin-right: 0px; margin-bottom: 15px; margin-left: 0px; padding: 0px; list-style: none; line-height: 1.5; font-family: Raleway, sans-serif;"><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">libero justo elit dolor vivamus adipiscing elit vivamus vulputat libero justo elit</li><li style="margin: 0px 0px 0px 15px; padding: 0px; list-style: disc; line-height: 1.5;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputat</li></ul></ul>';
        $job_candidate->jobs_description = '<p><span style="font-family: Raleway, sans-serif;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputate libero justo elit</span><br></p>';
        $job_candidate->skill_description = '<p><span style="font-family: Raleway, sans-serif;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputate libero justo elit dolor Vivamus adipiscng elit vivamus vulputat libero justo. Elit dolor ipsum dolor sit amet. Consectetur ipsum dolor amet.</span></p><p><span style="font-family: Raleway, sans-serif;">Lorem ipsum dolor sit amet. Consectetur adipiscing elit. Vivamus vulputate libero justo elit dolor Vivamus adipiscng elit vivamus vulputat libero justo. Elit dolor ipsum dolor sit amet. Consectetur ipsum dolor amet.</span><span style="font-family: Raleway, sans-serif;"><br></span></p>';
        $job_candidate->company_name = 'WorkDo Infotech';

        $job_candidate->JobExperience = [
            [
                'start_date'=> '01-03-2023',
                'end_date'=> '01-03-2024',
                'title'=> '<Title>',
                'organization'=> '<Organization>',
                'description'=> '<Description>'
            ],
        ];
        $job_candidate->JobProject = [
            [
                'start_date' => '01-03-2024',
                'end_date' => '01-03-2023',
                'title' => '<Title>',
                'organization'=> '<Organization>',
                'description' => '<Description>',
            ]
        ];
        $job_candidate->JobQualification = [
            [
                'start_date' => '01-03-2024',
                'end_date' => '01-03-2023',
                'title' => '<Title>',
                'organization'=> '<Organization>',
                'description' => '<Description>',
            ]
        ];
        $job_candidate->JobAward = [
            [
                'description' => '<Description>',
            ]
        ];
        $job_candidate->JobExperienceCandidate = [
            [
                'start_date' => '01-03-2024',
                'end_date' => '01-03-2023',
                'description' => '<Description>',
            ]
        ];
        $job_candidate->JobSkill = [
            [
                'description' => '<Description>'
            ]
        ];

        $preview = 1;
        $color = '#' . $color;

        $font_color = JobCandidate::getFontColor($color);

        $default_logo = get_file('uploads/users-avater/avatar.png');
        $company_settings = getCompanyAllSetting();

        $job_logo = isset($company_settings['job_logo']) ? $company_settings['job_logo'] : '';

        if (isset($job_logo) && !empty($job_logo)) {
            $img = get_file($job_logo);
        } else {
            $img = $default_logo;
        }

        $company_id = $job_candidates->created_by;
        $workspace = $job_candidates->workspace;
        $settings['company_name'] = isset($company_settings['company_name']) ? $company_settings['company_name'] : '';
        $settings['site_rtl'] = isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : '';
        $settings['company_email'] = isset($company_settings['company_email']) ? $company_settings['company_email'] : '';
        $settings['company_telephone'] = isset($company_settings['company_telephone']) ? $company_settings['company_telephone'] : '';
        $settings['company_address'] = isset($company_settings['company_address']) ? $company_settings['company_address'] : '';
        $settings['company_city'] = isset($company_settings['company_city']) ? $company_settings['company_city'] : '';
        $settings['company_state'] = isset($company_settings['company_state']) ? $company_settings['company_state'] : '';
        $settings['company_zipcode'] = isset($company_settings['company_zipcode']) ? $company_settings['company_zipcode'] : '';
        $settings['company_country'] = isset($company_settings['company_country']) ? $company_settings['company_country'] : '';
        $settings['registration_number'] = isset($company_settings['registration_number']) ? $company_settings['registration_number'] : '';
        $settings['tax_type'] = isset($company_settings['tax_type']) ? $company_settings['tax_type'] : '';
        $settings['vat_number'] = isset($company_settings['vat_number']) ? $company_settings['vat_number'] : '';
        return view('recruitment::templates.' . $template, compact('preview', 'color', 'settings', 'img', 'font_color', 'job_candidate', 'company_id', 'workspace', 'job_experiences', 'job_projects', 'job_qualifications', 'job_awards', 'job_experience_candidates', 'job_skills'));
    }

    public function saveJobTemplateSettings(Request $request)
    {
        $user = \Auth::user();
        $validator = \Validator::make(
            $request->all(),
            [
                'job_template' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        if ($request->job_logo) {
            $request->validate(
                [
                    'job_logo' => 'image',
                ]
            );

            $job_logo = $user->id . '_job_logo_' . time() . '.png';
            $uplaod = upload_file($request, 'job_logo', $job_logo, 'job_logo');
            if ($uplaod['flag'] == 1) {
                $url = $uplaod['url'];
                $old_job_logo = company_setting('job_logo');
                if (!empty($old_job_logo) && check_file($old_job_logo)) {
                    delete_file($old_job_logo);
                }
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }
        $post = $request->all();
        unset($post['_token']);

        if (isset($post['job_template']) && (!isset($post['job_color']) || empty($post['job_color']))) {
            $post['job_color'] = "ffffff";
        }
        if (isset($post['job_logo'])) {
            $post['job_logo'] = $url;
        }
        foreach ($post as $key => $value) {
            // Define the data to be updated or inserted
            $data = [
                'key' => $key,
                'workspace' => getActiveWorkSpace(),
                'created_by' => Auth::user()->id,
            ];
            // Check if the record exists, and update or insert accordingly
            Setting::updateOrInsert($data, ['value' => $value]);
        }
        // Settings Cache forget
        comapnySettingCacheForget();
        return redirect()->back()->with('success', 'Recruitment Print setting save sucessfully.');
    }

    public function DownloadResume($resume_id)
    {
        try {
            $resumeId = Crypt::decrypt($resume_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Resume Not Found.'));
        }

        $job_candidate = JobCandidate::with(
            [
                'JobExperience',
                'JobProject',
                'JobExperienceCandidate',
                'JobQualification',
                'JobSkill',
                'JobAward'
            ]
        )->where('id', $resumeId)->first();


        if ($job_candidate) {

            $company_settings = getCompanyAllSetting($job_candidate->created_by, $job_candidate->workspace);

            $color = '#' . (!empty($company_settings['job_color']) ? $company_settings['job_color'] : 'ffffff');

            $font_color = JobCandidate::getFontColor($color);

            $default_logo = get_file('uploads/users-avatar/avatar.png');

            $resume_profile = isset($job_candidate->profile) ? $job_candidate->profile : '';

            if (isset($resume_profile) && !empty($resume_profile)) {
                $img = get_file($resume_profile);
            } else {
                $img = $default_logo;
            }
            $company_id = $job_candidate->created_by;
            $workspace = $job_candidate->workspace;
            $settings['company_name'] = isset($company_settings['company_name']) ? $company_settings['company_name'] : '';
            $settings['site_rtl'] = isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : '';
            $settings['company_email'] = isset($company_settings['company_email']) ? $company_settings['company_email'] : '';
            $settings['company_telephone'] = isset($company_settings['company_telephone']) ? $company_settings['company_telephone'] : '';
            $settings['company_address'] = isset($company_settings['company_address']) ? $company_settings['company_address'] : '';
            $settings['company_city'] = isset($company_settings['company_city']) ? $company_settings['company_city'] : '';
            $settings['company_state'] = isset($company_settings['company_state']) ? $company_settings['company_state'] : '';
            $settings['company_zipcode'] = isset($company_settings['company_zipcode']) ? $company_settings['company_zipcode'] : '';
            $settings['company_country'] = isset($company_settings['company_country']) ? $company_settings['company_country'] : '';
            $settings['registration_number'] = isset($company_settings['registration_number']) ? $company_settings['registration_number'] : '';
            $settings['tax_type'] = isset($company_settings['tax_type']) ? $company_settings['tax_type'] : '';
            $settings['vat_number'] = isset($company_settings['vat_number']) ? $company_settings['vat_number'] : '';
            $settings['job_template'] = isset($company_settings['job_template']) ? $company_settings['job_template'] : '';
            return view('recruitment::templates.' . $settings['job_template'], compact('job_candidate', 'color', 'settings', 'img', 'font_color', 'company_id', 'workspace'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
