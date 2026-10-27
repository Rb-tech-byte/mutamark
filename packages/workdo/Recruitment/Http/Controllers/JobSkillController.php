<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Entities\JobSkill;
use Modules\Recruitment\Events\CreateJobSkill;
use Modules\Recruitment\Events\DestroyJobSkill;
use Modules\Recruitment\Events\UpdateJobSkill;

class JobSkillController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back();
        return view('recruitment::JobSkill.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('job skill create')) {
            $jobCandidateId = $request->query('id');
            $reference = JobCandidate::$reference;
            return view('recruitment::JobSkill.create', compact('reference', 'jobCandidateId'));
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
        if (Auth::user()->isAbleTo('job skill create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'organization' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required|after_or_equal:start_date',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first())->with('experience-tab', true);
            }

            $job_skill               = new JobSkill();
            $job_skill->candidate_id = $request->candidate_id;
            $job_skill->type         = $request->type;
            $job_skill->title        = $request->title;
            $job_skill->organization = $request->organization;
            $job_skill->start_date   = $request->start_date;
            $job_skill->end_date     = $request->end_date;
            $job_skill->country      = $request->country;
            $job_skill->state        = $request->state;
            $job_skill->city         = $request->city;
            $job_skill->reference    = $request->reference;
            $job_skill->description  = $request->description;
            $job_skill->workspace    = getActiveWorkSpace();
            $job_skill->created_by   = creatorId();
            $job_skill->save();

            event(new CreateJobSkill($request, $job_skill));

            return redirect()->back()->with('success', __('Job skill successfully created.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job skill create')) {
            $job_skill = JobSkill::find($id);

            return view('recruitment::JobSkill.show', compact('job_skill'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('job skill edit')) {
            $job_skill = JobSkill::find($id);
            $reference = JobCandidate::$reference;

            return view('recruitment::JobSkill.edit', compact('job_skill', 'reference'));
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
    public function update(Request $request, JobSkill $job_skill)
    {
        if (Auth::user()->isAbleTo('job skill edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'organization' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required|after_or_equal:start_date',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first())->with('experience-tab', true);
            }

            $job_skill->type        = $request->type;
            $job_skill->title        = $request->title;
            $job_skill->organization = $request->organization;
            $job_skill->start_date   = $request->start_date;
            $job_skill->end_date     = $request->end_date;
            $job_skill->country      = $request->country;
            $job_skill->state        = $request->state;
            $job_skill->city         = $request->city;
            $job_skill->reference    = $request->reference;
            $job_skill->description  = $request->description;
            $job_skill->workspace    = getActiveWorkSpace();
            $job_skill->created_by   = creatorId();
            $job_skill->save();

            event(new UpdateJobSkill($request, $job_skill));

            return redirect()->back()->with('success', __('Job skill successfully updated.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job skill delete')) {
            $job_skill = JobSkill::find($id);

            event(new DestroyJobSkill($job_skill));

            $job_skill->delete();

            return redirect()->back()->with('success', __('Job skill successfully deleted.'))->with('experience-tab', true);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
