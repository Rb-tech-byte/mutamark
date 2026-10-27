<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Recruitment\Entities\JobExperience;
use Modules\Recruitment\Events\CreateJobExperience;
use Modules\Recruitment\Events\DestroyJobExperience;
use Modules\Recruitment\Events\UpdateJobExperience;

class JobExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back();
        return view('recruitment::JobExperience.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('job experience create')) {
            $jobCandidateId = $request->query('id');
            return view('recruitment::JobExperience.create', compact('jobCandidateId'));
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
        if (Auth::user()->isAbleTo('job experience create')) {
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
                    'zipcode' => 'required',
                    'address' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first())->with('experience-tab', true);
            }

            if (!empty($request->experience_proof)) {

                $filenameWithExt = $request->file('experience_proof')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('experience_proof')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'experience_proof', $fileNameToStore, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    $url = $uplaod['url'];
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            if (isset($request->is_reference_slider) && $request->is_reference_slider == 'on') {
                $is_reference_slider = 1;
            } else {
                $is_reference_slider = 0;
            }

            $job_experience                      = new JobExperience();
            $job_experience->candidate_id        = $request->candidate_id;
            $job_experience->title               = $request->title;
            $job_experience->organization        = $request->organization;
            $job_experience->start_date          = $request->start_date;
            $job_experience->end_date            = $request->end_date;
            $job_experience->country             = $request->country;
            $job_experience->state               = $request->state;
            $job_experience->city                = $request->city;
            $job_experience->zipcode             = $request->zipcode;
            $job_experience->address             = $request->address;
            $job_experience->phone               = !empty($request->phone) ? $request->phone : '';
            $job_experience->email               = !empty($request->email) ? $request->email : '';
            $job_experience->website             = !empty($request->website) ? $request->website : '';
            $job_experience->experience_proof    = !empty($request->experience_proof) ? $url : '';
            $job_experience->is_reference_slider = $is_reference_slider;
            if ($is_reference_slider == 1) {
                $job_experience->full_name           = $request->full_name;
                $job_experience->reference_email     = $request->reference_email;
                $job_experience->reference_phone     = $request->reference_phone;
                $job_experience->job_position        = $request->job_position;
            } else {
                $job_experience->full_name           = '';
                $job_experience->reference_email     = '';
                $job_experience->reference_phone     = '';
                $job_experience->job_position        = '';
            }
            $job_experience->description        = $request->description;
            $job_experience->workspace           = getActiveWorkSpace();
            $job_experience->created_by          = creatorId();
            $job_experience->save();

            event(new CreateJobExperience($request, $job_experience));

            return redirect()->back()->with('success', __('Job Experience successfully created.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job experience show')) {
            $job_experience = JobExperience::find($id);

            return view('recruitment::JobExperience.show', compact('job_experience'));
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
        if (Auth::user()->isAbleTo('job experience edit')) {
            $job_experience = JobExperience::find($id);
            return view('recruitment::JobExperience.edit', compact('job_experience'));
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
        if (Auth::user()->isAbleTo('job experience edit')) {
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
                    'zipcode' => 'required',
                    'address' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first())->with('experience-tab', true);
            }

            $job_experience = JobExperience::find($id);

            if (!empty($request->experience_proof)) {
                $filenameWithExt = $request->file('experience_proof')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('experience_proof')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'experience_proof', $fileNameToStore, 'JobApplication');
                if ($uplaod['flag'] == 1) {
                    if (!empty($job_experience->experience_proof)) {
                        delete_file($job_experience->experience_proof);
                    }
                    $url = $uplaod['url'];
                    $job_experience->experience_proof = $url;
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            if (isset($request->is_reference_slider) && $request->is_reference_slider == 'on') {
                $is_reference_slider = 1;
            } else {
                $is_reference_slider = 0;
            }

            $job_experience->title               = $request->title;
            $job_experience->organization        = $request->organization;
            $job_experience->start_date          = $request->start_date;
            $job_experience->end_date            = $request->end_date;
            $job_experience->country             = $request->country;
            $job_experience->state               = $request->state;
            $job_experience->city                = $request->city;
            $job_experience->zipcode             = $request->zipcode;
            $job_experience->address             = $request->address;
            $job_experience->phone               = !empty($request->phone) ? $request->phone : '';
            $job_experience->email               = !empty($request->email) ? $request->email : '';
            $job_experience->website             = !empty($request->website) ? $request->website : '';
            $job_experience->is_reference_slider = $is_reference_slider;
            if ($is_reference_slider == 1) {
                $job_experience->full_name           = $request->full_name;
                $job_experience->reference_email     = $request->reference_email;
                $job_experience->reference_phone     = $request->reference_phone;
                $job_experience->job_position        = $request->job_position;
            } else {
                $job_experience->full_name           = '';
                $job_experience->reference_email     = '';
                $job_experience->reference_phone     = '';
                $job_experience->job_position        = '';
            }
            $job_experience->description        = $request->description;
            $job_experience->workspace           = getActiveWorkSpace();
            $job_experience->created_by          = creatorId();
            $job_experience->save();

            event(new UpdateJobExperience($request, $job_experience));

            return redirect()->back()->with('success', __('Job Experience successfully updated.'))->with('experience-tab', true);
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
        if (Auth::user()->isAbleTo('job experience delete')) {
            $currentWorkspace = getActiveWorkSpace();
            $job_experience = JobExperience::find($id);
            if ($job_experience->created_by == creatorId() && $job_experience->workspace == $currentWorkspace) {

                event(new DestroyJobExperience($job_experience));

                if (!empty($job_experience->experience_proof)) {
                    delete_file($job_experience->experience_proof);
                }

                $job_experience->delete();
                return redirect()->back()->with('success', __('Job Experience successfully deleted.'))->with('experience-tab', true);
            } else {
                return redirect()->back()->with('error', 'Permission denied.');
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
