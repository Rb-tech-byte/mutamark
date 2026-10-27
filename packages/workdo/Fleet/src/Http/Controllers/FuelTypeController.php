<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\FuelType;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Events\CreateFuelType;
use Workdo\Fleet\Events\DestroyFuelType;
use Workdo\Fleet\Events\UpdateFuelType;

class FuelTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (\Auth::user()->isAbleTo('fueltype manage')) {
            $fuelTypes = FuelType::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('fleet::FuelType.index', compact('fuelTypes'));
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
        if (Auth::user()->isAbleTo('fueltype create')) {

            return view('fleet::FuelType.create');
        } else {
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
        if(\Auth::user()->isAbleTo('fueltype create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('fuelType.index')->with('error', $messages->first());
            }

            $fuelType             = new FuelType();
            $fuelType->name       = $request->name;
            $fuelType->workspace  = getActiveWorkSpace();
            $fuelType->created_by = creatorId();
            $fuelType->save();

            event(new CreateFuelType($request,$fuelType));
            return redirect()->route('fuelType.index')->with('success', __('The fuel type has been created successfully.'));
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
    public function edit(FuelType $fuelType)
    {
        if (Auth::user()->isAbleTo('fueltype edit')) {

            return view('fleet::FuelType.edit',compact('fuelType'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, FuelType $fuelType)
    {
        if(\Auth::user()->isAbleTo('fueltype edit'))
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

            $fuelType->name       = $request->name;
            $fuelType->workspace  = getActiveWorkSpace();
            $fuelType->created_by = creatorId();
            $fuelType->save();
            event(new UpdateFuelType($request,$fuelType));

            return redirect()->route('fuelType.index')->with('success', __('The fuel type details are updated successfully.'));
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
    public function destroy(FuelType $fuelType)
    {
        if(\Auth::user()->isAbleTo('fueltype delete'))
        {
            $fuelTypes = Vehicle::where('fuel_type', $fuelType->id)->first();
            if(!empty($fuelTypes))
            {
                return redirect()->back()->with('error', __('this FuelType is already use so please transfer or delete this FuelType related data.'));
            }
            event(new DestroyFuelType($fuelType));
            $fuelType->delete();

            return redirect()->route('fuelType.index')->with('success', 'The fuel type has been deleted.');
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
