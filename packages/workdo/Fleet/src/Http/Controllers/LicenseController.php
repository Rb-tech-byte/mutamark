<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\License;
use Workdo\Fleet\Events\CreateLicense;
use Workdo\Fleet\Events\DestroyLicense;
use Workdo\Fleet\Events\UpdateLicense;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('license manage')) {
            $licenses = License::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('fleet::license.index', compact('licenses'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('license create')) {
            return view('fleet::license.create');
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
        if (\Auth::user()->isAbleTo('license create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('license.index')->with('error', $messages->first());
            }

            $License             = new License();
            $License->name       = $request->name;
            $License->workspace  = getActiveWorkSpace();
            $License->created_by = creatorId();
            $License->save();
            event(new CreateLicense($request,$License));

            return redirect()->route('license.index')->with('success', __('The license has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
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
        return view('fleet::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(License $license)
    {
        if (Auth::user()->isAbleTo('license edit')) {
            if ($license->created_by == creatorId() && $license->workspace == getActiveWorkSpace()) {
                return view('fleet::license.edit', compact('license'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
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
    public function update(Request $request, License $license)
    {
        if (\Auth::user()->isAbleTo('license edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $license->name       = $request->name;
            $license->workspace  = getActiveWorkSpace();
            $license->created_by = creatorId();
            $license->save();
            event(new UpdateLicense($request,$license));

            return redirect()->route('license.index')->with('success', __('The license details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(License $license)
    {
        if (\Auth::user()->isAbleTo('license delete')) {
            if ($license->created_by == creatorId() && $license->workspace == getActiveWorkSpace()) {
                $licenseData = Driver::where('lincese_type', $license->id)->first();
                if (!empty($licenseData)) {
                    return redirect()->back()->with('error', __('this License is already use so please transfer or delete this License related data.'));
                }
                event(new DestroyLicense($license));

                $license->delete();

                return redirect()->route('license.index')->with('success', 'The license has been deleted.');
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
