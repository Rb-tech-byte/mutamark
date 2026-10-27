<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Entities\VehicleType;
use Workdo\Fleet\Events\CreateVehicleType;
use Workdo\Fleet\Events\DestroyVehicleType;
use Workdo\Fleet\Events\UpdateVehicleType;
use Workdo\Fleet\Entities\VehicleInvoice;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->isAbleTo('vehicletype manage'))
        {
            $VehicleTypes = VehicleType::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('fleet::vehicleType.index',compact('VehicleTypes'));
        }
        else{
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('vehicletype create')) {
            return view('fleet::vehicleType.create');
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
        if(\Auth::user()->isAbleTo('vehicletype create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('vehicleType.index')->with('error', $messages->first());
            }

            $vehicleType             = new VehicleType();
            $vehicleType->name       = $request->name;
            $vehicleType->workspace  = getActiveWorkSpace();
            $vehicleType->created_by = creatorId();
            $vehicleType->save();

            event(new CreatevehicleType($request,$vehicleType));

            return redirect()->route('vehicleType.index')->with('success', __('The vehicle type has been created successfully.'));
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
    public function edit(VehicleType $vehicleType)
    {
        if (Auth::user()->isAbleTo('vehicletype edit')) {
            if ($vehicleType->created_by == creatorId() && $vehicleType->workspace == getActiveWorkSpace()) {
                return view('fleet::vehicleType.edit', compact('vehicleType'));
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
    public function update(Request $request, VehicleType $vehicleType)
    {
        if(\Auth::user()->isAbleTo('vehicletype edit'))
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

            $vehicleType->name       = $request->name;
            $vehicleType->workspace  = getActiveWorkSpace();
            $vehicleType->created_by = creatorId();
            $vehicleType->save();

            event(new UpdateVehicleType($request,$vehicleType));

            return redirect()->route('vehicleType.index')->with('success', __('The vehicle type details are updated successfully.'));
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
    public function destroy(VehicleType $vehicleType)
    {
        if(Auth::user()->isAbleTo('vehicletype delete'))
        {
            $VehicleType = Vehicle::where('vehicle_type', $vehicleType->id)->first();
            if(!empty($VehicleType))
            {
                return redirect()->back()->with('error', __('this VehicleType is already use so please transfer or delete this VehicleType related data.'));
            }
            event(new DestroyVehicleType($vehicleType));

            $vehicleType->delete();

            return redirect()->route('vehicleType.index')->with('success', 'The vehicle type has been deleted.' );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }



    public function items(Request $request)
    {
        $data   = VehicleInvoice::where('invoice_id', $request->sale_id)->where('item',$request->item)->first();
        if($data != null)
        {
            $data = $data->toArray();
            return json_encode($data);
        }
        else
        {
            abort(404);
        }
    }
    
}
