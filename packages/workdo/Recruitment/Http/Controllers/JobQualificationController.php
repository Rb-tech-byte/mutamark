<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Entities\JobQualification;
use Modules\Recruitment\Events\CreateJobQualification;
use Modules\Recruitment\Events\DestroyJobQualification;
use Modules\Recruitment\Events\UpdateJobQualification;

class JobQualificationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back();
        return view('recruitment::JobQualification.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('job qualification create')) {
            $jobCandidateId = $request->query('id');
            $reference = JobCandidate::$reference;
            return view('recruitment::JobQualification.create', compact('reference', 'jobCandidateId'));
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
        if (Auth::user()->isAbleTo('job qualification create')) {
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

            $job_qualification               = new JobQualification();
            $job_qualification->candidate_id = $request->candidate_id;
            $job_qualification->title        = $request->title;
            $job_qualification->organization = $request->organization;
            $job_qualification->start_date   = $request->start_date;
            $job_qualification->end_date     = $request->end_date;
            $job_qualification->country      = $request->country;
            $job_qualification->state        = $request->state;
            $job_qualification->city         = $request->city;
            $job_qualification->reference    = $request->reference;
            $job_qualification->description  = $request->description;
            $job_qualification->workspace    = getActiveWorkSpace();
            $job_qualification->created_by   = creatorId();
            $job_qualification->save();

            event(new CreateJobQualification($request, $job_qualification));

            return redirect()->back()->with('success', __('Job Qualification successfully created.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job qualification show')) {
            $job_qualification = JobQualification::find($id);

            return view('recruitment::JobQualification.show', compact('job_qualification'));
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
        if (Auth::user()->isAbleTo('job qualification edit')) {
            $job_qualification = JobQualification::find($id);
            $reference = JobCandidate::$reference;
            return view('recruitment::JobQualification.edit', compact('job_qualification', 'reference'));
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
    public function update(Request $request, JobQualification $job_qualification)
    {
        if (Auth::user()->isAbleTo('job qualification edit')) {
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

            $job_qualification->title        = $request->title;
            $job_qualification->organization = $request->organization;
            $job_qualification->start_date   = $request->start_date;
            $job_qualification->end_date     = $request->end_date;
            $job_qualification->country      = $request->country;
            $job_qualification->state        = $request->state;
            $job_qualification->city         = $request->city;
            $job_qualification->reference    = $request->reference;
            $job_qualification->description  = $request->description;
            $job_qualification->workspace    = getActiveWorkSpace();
            $job_qualification->created_by   = creatorId();
            $job_qualification->save();

            event(new UpdateJobQualification($request, $job_qualification));

            return redirect()->back()->with('success', __('Job Qualification successfully updated.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job qualification delete')) {
            $job_qualification = JobQualification::find($id);

            event(new DestroyJobQualification($job_qualification));

            $job_qualification->delete();

            return redirect()->back()->with('success', __('Job Qualification successfully deleted.'))->with('experience-tab', true);
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
