<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Entities\JobProject;
use Modules\Recruitment\Events\CreateJobProject;
use Modules\Recruitment\Events\DestroyJobProject;
use Modules\Recruitment\Events\UpdateJobProject;

class JobProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back();
        return view('recruitment::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {

        if (Auth::user()->isAbleTo('job project create')) {
            $jobCandidateId = $request->query('id');
            $reference = JobCandidate::$reference;
            return view('recruitment::JobProject.create', compact('reference', 'jobCandidateId'));
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
        if (Auth::user()->isAbleTo('job project create')) {
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

            $job_project               = new JobProject();
            $job_project->candidate_id = $request->candidate_id;
            $job_project->title        = $request->title;
            $job_project->organization = $request->organization;
            $job_project->start_date   = $request->start_date;
            $job_project->end_date     = $request->end_date;
            $job_project->country      = $request->country;
            $job_project->state        = $request->state;
            $job_project->city         = $request->city;
            $job_project->reference    = $request->reference;
            $job_project->description  = $request->description;
            $job_project->workspace    = getActiveWorkSpace();
            $job_project->created_by   = creatorId();
            $job_project->save();

            event(new CreateJobProject($request, $job_project));

            return redirect()->back()->with('success', __('Job project successfully created.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job project show')) {
            $job_project = JobProject::find($id);

            return view('recruitment::JobProject.show', compact('job_project'));
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
        if (Auth::user()->isAbleTo('job project edit')) {
            $job_project = JobProject::find($id);
            $reference = JobCandidate::$reference;
            return view('recruitment::JobProject.edit', compact('job_project', 'reference'));
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
    public function update(Request $request, JobProject $job_project)
    {
        if (Auth::user()->isAbleTo('job project edit')) {
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

            $job_project->title        = $request->title;
            $job_project->organization = $request->organization;
            $job_project->start_date   = $request->start_date;
            $job_project->end_date     = $request->end_date;
            $job_project->country      = $request->country;
            $job_project->state        = $request->state;
            $job_project->city         = $request->city;
            $job_project->reference    = $request->reference;
            $job_project->description  = $request->description;
            $job_project->workspace    = getActiveWorkSpace();
            $job_project->created_by   = creatorId();
            $job_project->save();

            event(new UpdateJobProject($request, $job_project));

            return redirect()->back()->with('success', __('Job project successfully updated.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job project delete')) {
            $job_project = JobProject::find($id);

            event(new DestroyJobProject($job_project));

            $job_project->delete();

            return redirect()->back()->with('success', __('Job project successfully deleted.'))->with('experience-tab', true);
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
