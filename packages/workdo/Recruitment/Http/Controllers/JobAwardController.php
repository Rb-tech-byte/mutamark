<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobAward;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Events\CreateJobAward;
use Modules\Recruitment\Events\DestroyJobAward;
use Modules\Recruitment\Events\UpdateJobAward;

class JobAwardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back();
        return view('recruitment::JobAward.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('job award create')) {
            $jobCandidateId = $request->query('id');
            $reference = JobCandidate::$reference;
            return view('recruitment::JobAward.create', compact('reference', 'jobCandidateId'));
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
        if (Auth::user()->isAbleTo('job award create')) {
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

            $job_award               = new JobAward();
            $job_award->candidate_id = $request->candidate_id;
            $job_award->title        = $request->title;
            $job_award->organization = $request->organization;
            $job_award->start_date   = $request->start_date;
            $job_award->end_date     = $request->end_date;
            $job_award->country      = $request->country;
            $job_award->state        = $request->state;
            $job_award->city         = $request->city;
            $job_award->reference    = $request->reference;
            $job_award->description  = $request->description;
            $job_award->workspace    = getActiveWorkSpace();
            $job_award->created_by   = creatorId();
            $job_award->save();
            
            event(new CreateJobAward($request, $job_award));

            return redirect()->back()->with('success', __('Job Award successfully created.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job award show')) {
            $job_award = JobAward::find($id);

            return view('recruitment::JobAward.show', compact('job_award'));
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
        if (Auth::user()->isAbleTo('job award edit')) {
            $job_award = JobAward::find($id);
            $reference = JobCandidate::$reference;

            return view('recruitment::JobAward.edit', compact('job_award', 'reference'));
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
    public function update(Request $request, JobAward $job_award)
    {
        if (Auth::user()->isAbleTo('job award edit')) {
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

            $job_award->title        = $request->title;
            $job_award->organization = $request->organization;
            $job_award->start_date   = $request->start_date;
            $job_award->end_date     = $request->end_date;
            $job_award->country      = $request->country;
            $job_award->state        = $request->state;
            $job_award->city         = $request->city;
            $job_award->reference    = $request->reference;
            $job_award->description  = $request->description;
            $job_award->workspace    = getActiveWorkSpace();
            $job_award->created_by   = creatorId();
            $job_award->save();

            event(new UpdateJobAward($request, $job_award));

            return redirect()->back()->with('success', __('Job Award successfully updated.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job award delete')) {
            $job_award = JobAward::find($id);

            event(new DestroyJobAward($job_award));

            $job_award->delete();

            return redirect()->back()->with('success', __('Job Award successfully deleted.'))->with('experience-tab', true);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
