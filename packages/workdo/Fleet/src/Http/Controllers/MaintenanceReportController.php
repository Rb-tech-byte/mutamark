<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Entities\Maintenance;


class MaintenanceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if(\Auth::user()->isAbleTo('maintenance manage'))
        {
            $vehicles = Vehicle::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            $maintenances = Maintenance::where('created_by', creatorId())->where('workspace', getActiveWorkSpace());
            if(isset($request->vehicle)){
                    $maintenances = $maintenances->where('vehicle_name',$request->vehicle);
            }
            $maintenances = $maintenances->get();

            $totalcharge = 0;
            $totalchargebear = 0;
            $totalcost = 0;

            foreach ($maintenances as $key => $maintenance) {
                $totalcharge += $maintenance->charge;
                $totalchargebear += $maintenance->charge_bear_by;
                $totalcost += $maintenance->total_cost;
            }
            $currentYear = date('Y');

            $incExpBarChartData = Maintenance::getincExpBarChartData($request);


            return view('fleet::MaintenanceReport.index',compact('vehicles','maintenances','totalcharge','totalchargebear','totalcost','incExpBarChartData','currentYear'));

        }
        else
        {
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
