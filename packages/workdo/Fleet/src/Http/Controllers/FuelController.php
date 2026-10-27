<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\FuelType;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Events\CreateFuel;
use Workdo\Fleet\Events\DestroyFuel;
use Workdo\Fleet\Events\UpdateFuel;
use Workdo\Fleet\DataTables\FuelDataTable;

class FuelController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(FuelDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('fuel manage')) {
                return $dataTable->render('fleet::fuel.index');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('fuel create')) {

            $driver = Driver::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->with('client')->get();
            $vehicle = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $fuelType = FuelType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Fuel Type', '');
            return view('fleet::fuel.create', compact('driver', 'vehicle','fuelType'));
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (\Auth::user()->isAbleTo('fuel create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle_name' => 'required',
                    'fill_date' => 'required',
                    'fuel_type' => 'required',
                    'quantity' => 'required',
                    'cost' => 'required',
                    'total_cost' => 'required',
                    'odometer_reading' => 'required',
                    'notes' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $fuel = new Fuel();
            $fuel->driver_name         = isset($request->driver_name) ? $request->driver_name : '';
            $fuel->vehicle_name        = $request->vehicle_name;
            $fuel->fill_date           = $request->fill_date;
            $fuel->fuel_type           = $request->fuel_type;
            $fuel->quantity            = $request->quantity;
            $fuel->cost                = $request->cost;
            $fuel->total_cost          = $request->total_cost;
            $fuel->odometer_reading    = $request->odometer_reading;
            $fuel->notes               = $request->notes;
            $fuel->workspace           = getActiveWorkSpace();
            $fuel->created_by          = creatorId();
            $fuel->save();
            event(new CreateFuel($request,$fuel));

            return redirect()->route('fuel.index')->with('success', __('The fuel has been created successfully.'));
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
        return redirect()->back();
        return view('fleet::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Fuel $fuel)
    {
        if (\Auth::user()->isAbleTo('fuel edit')) {
            $driver = Driver::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->with('client')->get();
            $vehicle = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $fuelType = FuelType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Fuel Type', '');
            return view('fleet::fuel.edit', compact('fuel', 'driver', 'vehicle','fuelType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Fuel $fuel)
    {
        if (\Auth::user()->isAbleTo('fuel edit')) {
            $validator = \Validator::make(
                $request->all(), [
                        'vehicle_name' => 'required',
                        'fill_date' => 'required',
                        'fuel_type' => 'required',
                        'quantity' => 'required',
                        'cost' => 'required',
                        'total_cost' => 'required',
                        'odometer_reading' => 'required',
                        'notes' => 'required', 
                        ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $fuel->driver_name       = \Auth::user()->type == "company" ? $request['driver_name'] : \Auth::user()->id;
            $fuel->vehicle_name      = $request->vehicle_name;
            $fuel->fill_date         = $request->fill_date;
            $fuel->quantity          = $request->quantity;
            $fuel->cost            = $request->cost;
            $fuel->total_cost            = $request->total_cost;
            $fuel->odometer_reading  = $request->odometer_reading;
            $fuel->notes       = $request->notes;
            $fuel->workspace      = getActiveWorkSpace();
            $fuel->created_by        = creatorId();
            $fuel->save();
            event(new UpdateFuel($request,$fuel));

            return redirect()->route('fuel.index')->with('success', __('The fuel details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Fuel $fuel)
    {
        if(\Auth::user()->isAbleTo('fuel delete'))
        {
            event(new DestroyFuel($fuel));
            $fuel->delete();

            return redirect()->route('fuel.index')->with('success', 'The fuel has been deleted..' );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
