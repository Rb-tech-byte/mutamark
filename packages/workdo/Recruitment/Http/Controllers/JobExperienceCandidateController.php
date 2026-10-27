<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Entities\JobExperienceCandidate;
use Modules\Recruitment\Events\CreateJobExperienceCandidate;
use Modules\Recruitment\Events\DestroyJobExperienceCandidate;
use Modules\Recruitment\Events\UpdateJobExperienceCandidate;

class JobExperienceCandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back();
        return view('recruitment::JobExperienceCandidate.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('experience candidate job create')) {
            $jobCandidateId = $request->query('id');
            $reference = JobCandidate::$reference;
            return view('recruitment::JobExperienceCandidate.create', compact('reference', 'jobCandidateId'));
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
        if (Auth::user()->isAbleTo('experience candidate job create')) {
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

            $job_experience_candidate               = new JobExperienceCandidate();
            $job_experience_candidate->candidate_id = $request->candidate_id;
            $job_experience_candidate->title        = $request->title;
            $job_experience_candidate->organization = $request->organization;
            $job_experience_candidate->start_date   = $request->start_date;
            $job_experience_candidate->end_date     = $request->end_date;
            $job_experience_candidate->country      = $request->country;
            $job_experience_candidate->state        = $request->state;
            $job_experience_candidate->city         = $request->city;
            $job_experience_candidate->reference    = $request->reference;
            $job_experience_candidate->description  = $request->description;
            $job_experience_candidate->workspace    = getActiveWorkSpace();
            $job_experience_candidate->created_by   = creatorId();
            $job_experience_candidate->save();
            
            event(new CreateJobExperienceCandidate($request, $job_experience_candidate));

            return redirect()->back()->with('success', __('Experience Candidate Job successfully created.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('experience candidate job show')) {
            $job_experience_candidate = JobExperienceCandidate::find($id);

            return view('recruitment::JobExperienceCandidate.show', compact('job_experience_candidate'));
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
        if (Auth::user()->isAbleTo('experience candidate job edit')) {
            $job_experience_candidate = JobExperienceCandidate::find($id);
            $reference = JobCandidate::$reference;

            return view('recruitment::JobExperienceCandidate.edit', compact('job_experience_candidate', 'reference'));
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
    public function update(Request $request, JobExperienceCandidate $job_experience_candidate)
    {
        if (Auth::user()->isAbleTo('experience candidate job edit')) {
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

            $job_experience_candidate->title        = $request->title;
            $job_experience_candidate->organization = $request->organization;
            $job_experience_candidate->start_date   = $request->start_date;
            $job_experience_candidate->end_date     = $request->end_date;
            $job_experience_candidate->country      = $request->country;
            $job_experience_candidate->state        = $request->state;
            $job_experience_candidate->city         = $request->city;
            $job_experience_candidate->reference    = $request->reference;
            $job_experience_candidate->description  = $request->description;
            $job_experience_candidate->workspace    = getActiveWorkSpace();
            $job_experience_candidate->created_by   = creatorId();
            $job_experience_candidate->save();

            event(new UpdateJobExperienceCandidate($request, $job_experience_candidate));

            return redirect()->back()->with('success', __('Experience Candidate Job successfully updated.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('experience candidate job delete')) {
            $job_experience_candidate = JobExperienceCandidate::find($id);

            event(new DestroyJobExperienceCandidate($job_experience_candidate));

            $job_experience_candidate->delete();

            return redirect()->back()->with('success', __('Experience Candidate Job successfully deleted.'))->with('experience-tab', true);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
