<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\Vehicle;
use Illuminate\Support\Facades\Auth;

class FuelHistroyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

        if (\Auth::user()->isAbleTo('fuel manage')) {
            $vehicles = Vehicle::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $fuelTypes = Fuel::where('created_by',creatorId())->where('workspace', getActiveWorkSpace());
            if(isset($request->vehicle)){
                $fuelTypes = $fuelTypes->where('vehicle_name',$request->vehicle);
            }

            $fuelTypes = $fuelTypes->get();

            $total_quantity = 0;
            $total_cost = 0;
            $total_total_cost = 0;

            foreach ($fuelTypes as $key => $fuelType) {
                $total_quantity += $fuelType->quantity;
                $total_cost += $fuelType->cost;
                $total_total_cost += $fuelType->total_cost;
            }

            $currentYear = date('Y');

            $incExpBarChartData = Fuel::getincExpBarChartData($request);

            return view('fleet::FuelHistroyReport.index',compact('vehicles','fuelTypes','total_quantity','total_cost','total_total_cost','currentYear','incExpBarChartData'));

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
        return redirect()->back();
        return view('fleet::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return redirect()->back();
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
    public function edit($id)
    {
        return redirect()->back();
        return view('fleet::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return redirect()->back();
    }
}
