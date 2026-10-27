<?php

namespace Workdo\Fleet\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\Fleet\Entities\MaintenanceType;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Events\CreateMaintenances;
use Workdo\Fleet\Events\DestroyMaintenances;
use Workdo\Fleet\Events\UpdateMaintenances;
use Workdo\Fleet\DataTables\MaintenanceDataTable;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(MaintenanceDataTable $dataTable,Request $request)
    {
        if (Auth::user()->isAbleTo('maintenance manage')) {

            $employees = User::where('created_by', '=', creatorId())->emp()->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Employee', '');

            $MaintenanceType = MaintenanceType::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Maintenance Type', '');

            return $dataTable->render('fleet::maintenance.index', compact('employees', 'MaintenanceType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('maintenance create')) {
            $employees = User::where('created_by', '=', creatorId())->emp()->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Users', '');
            $vehicles = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $MaintenanceType = MaintenanceType::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select MaintenanceType', '');

            return view('fleet::maintenance.create', compact('employees', 'vehicles', 'MaintenanceType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('maintenance create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'service_type' => 'required',
                    'vehicle_name' => 'required',
                    'maintenance_type' => 'required',
                    'service_name' => 'required',
                    'charge' => 'required',
                    'charge_bear_by' => 'required',
                    'maintenance_date' => 'required',
                    'priority' => 'required',
                    'total_cost' => 'required',
                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->withInput()->with('error', $messages->first());
                }
                $Maintenances = Maintenance::create(
                [
                    'service_type'  => $request['service_type'],
                    'service_for'   => Auth::user()->type == 'company' ? $request['service_for'] : \Auth::user()->id,
                    'vehicle_name'      => $request['vehicle_name'],
                    'maintenance_type'  => $request['maintenance_type'],
                    'service_name'      => $request['service_name'],
                    'charge'            => $request['charge'],
                    'charge_bear_by'    => $request['charge_bear_by'],
                    'maintenance_date'      => $request['maintenance_date'],
                    'priority'          => $request['priority'],
                    'total_cost'        => $request['total_cost'],
                    'notes'       => $request['notes'],
                    'workspace'        => getActiveWorkSpace(),
                    'created_by'        => creatorId(),
                    ]
                );
                event(new CreateMaintenances($request,$Maintenances));

            return redirect()->route('maintenance.index')->with('success', __('The Maintenance has been created successfully.'));
        }
        else
        {
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
    public function edit(Maintenance $maintenance)
    {
        if (Auth::user()->isAbleTo('maintenance edit')) {
            $employees = User::where('created_by', '=', creatorId())->emp()->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Users', '');
            $vehicles = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $MaintenanceType = MaintenanceType::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select MaintenanceType', '');

            return view('fleet::maintenance.edit',compact('employees','vehicles','MaintenanceType','maintenance'));
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
    public function update(Request $request, Maintenance $maintenance)
    {
        if(Auth::user()->isAbleTo('maintenance edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'service_type' => 'required',
                                'vehicle_name' => 'required',
                                'maintenance_type' => 'required',
                                'service_name' => 'required',
                                'charge' => 'required',
                                'charge_bear_by' => 'required',
                                'maintenance_date' => 'required',
                                'priority' => 'required',
                                'total_cost' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $maintenance->service_type     = $request->service_type;
            $maintenance->service_for      = Auth::user()->type == 'company' ? $request['service_for'] : \Auth::user()->id;
            $maintenance->vehicle_name     = $request->vehicle_name;
            $maintenance->maintenance_type = $request->maintenance_type;
            $maintenance->service_name     = $request->service_name;
            $maintenance->charge           = $request->charge;
            $maintenance->charge_bear_by   = $request->charge_bear_by;
            $maintenance->maintenance_date = $request->maintenance_date;
            $maintenance->priority         = $request->priority;
            $maintenance->total_cost       = $request->total_cost;
            $maintenance->notes            = $request->notes;
            $maintenance->workspace        = getActiveWorkSpace();
            $maintenance->created_by       = creatorId();
            $maintenance->save();

            event(new UpdateMaintenances($request,$maintenance));

            return redirect()->route('maintenance.index')->with('success', __('The maintenance details are updated successfully.'));
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
    public function destroy(Maintenance $maintenance)
    {
        if(Auth::user()->isAbleTo('maintenance delete'))
        {
            event(new DestroyMaintenances($maintenance));

            $maintenance->delete();

            return redirect()->route('maintenance.index')->with('success', 'The maintenance has been deleted.' );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
