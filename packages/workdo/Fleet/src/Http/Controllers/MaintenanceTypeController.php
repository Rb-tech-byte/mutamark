<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\Fleet\Entities\MaintenanceType;
use Workdo\Fleet\Events\CreateMaintenanceType;
use Workdo\Fleet\Events\DestroyMaintenanceType;
use Workdo\Fleet\Events\UpdateMaintenanceType;

class MaintenanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->isAbleTo('maintenanceType manage'))
        {
            $maintenanceTypes = MaintenanceType::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('fleet::maintenanceType.index',compact('maintenanceTypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(\Auth::user()->isAbleTo('maintenanceType create'))
        {
             return view('fleet::maintenanceType.create');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(\Auth::user()->isAbleTo('maintenanceType create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('maintenanceType.index')->with('error', $messages->first());
            }

            $MaintenanceType             = new MaintenanceType();
            $MaintenanceType->name       = $request->name;
            $MaintenanceType->workspace  = getActiveWorkSpace();
            $MaintenanceType->created_by = creatorId();
            $MaintenanceType->save();

            event(new CreateMaintenanceType($request,$MaintenanceType));

            return redirect()->route('maintenanceType.index')->with('success', __('The maintenance type has been created successfully.'));
        }
        else
        {
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
        return redirect()->back()->with('error', __('Permission denied.'));
        return view('fleet::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(MaintenanceType $maintenanceType)
    {
        if(\Auth::user()->isAbleTo('maintenanceType edit'))
        {
             return view('fleet::maintenanceType.edit',compact('maintenanceType'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, MaintenanceType $maintenanceType)
    {
        if(\Auth::user()->isAbleTo('maintenanceType edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $maintenanceType->name       = $request->name;
            $maintenanceType->workspace  = getActiveWorkSpace();
            $maintenanceType->created_by = creatorId();
            $maintenanceType->save();
            event(new UpdateMaintenanceType($request,$maintenanceType));

            return redirect()->route('maintenanceType.index')->with('success', __('The maintenance type details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(MaintenanceType $maintenanceType)
    {
        if(\Auth::user()->isAbleTo('maintenanceType delete'))
        {
            $maintenanceTypes = Maintenance::where('maintenance_type', $maintenanceType->id)->first();
            if(!empty($maintenanceTypes))
            {
                return redirect()->back()->with('error', __('this maintenanceType is already use so please transfer or delete this maintenanceType related data.'));
            }
            event(new DestroyMaintenanceType($maintenanceType));

            $maintenanceType->delete();

            return redirect()->route('maintenanceType.index')->with('success', 'The maintenance type has been deleted.' );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
