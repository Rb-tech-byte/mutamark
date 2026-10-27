<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Entities\Driver;
use Workdo\ProductService\Entities\ProductService;
use Workdo\Fleet\Entities\Logbook;
use Workdo\Fleet\Entities\VehicleType;
use Workdo\Fleet\Events\CreateLogbook;
use Workdo\Fleet\Events\UpdateLogbook;
use Workdo\Fleet\Events\DestroyLogbook;
use Workdo\Fleet\DataTables\LogbookDataTable;

class LogbookController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(LogbookDataTable $dataTable,Request $request)
    {
        if(\Auth::user()->isAbleTo('fleet logbook manage'))
        {
            $vehicle = Vehicle::where('created_by', '=', creatorId())
                            ->where('workspace', getActiveWorkSpace())
                            ->pluck('name', 'id');

            $drivers = Driver::where('created_by', creatorId())
                            ->where('workspace', getActiveWorkSpace())
                            ->with('client')
                            ->get();

            return $dataTable->render('fleet::logbook.index', compact('vehicle', 'drivers'));
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
        if(\Auth::user()->isAbleTo('fleet logbook create'))
        {
            $vehicle = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $drivers = Driver::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('client')->get();
            $rate = ProductService::where('workspace_id', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('type','fleet')->get()->pluck('name','id');

            return view('fleet::logbook.create',compact('vehicle','drivers','rate'));
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
        if(\Auth::user()->isAbleTo('fleet logbook create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'driver_name' => 'required|max:120',
                                   'vehicle_name' => 'required|max:120',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'start_odometer' => 'required|max:255',
                                   'end_odometer' => 'required|max:255',
                                   'rate' => 'required',
                                   'total_distance' => 'required',
                                   'total_price' => 'required|max:20480',
                                    
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $logbook                  = new Logbook();
            $logbook->driver_name     = isset($request->driver_name) ? $request->driver_name :'';
            $logbook->vehicle_name    = isset($request->vehicle_name) ? $request->vehicle_name :'';
            $logbook->start_date      = isset($request->start_date) ? $request->start_date :'';
            $logbook->end_date        = isset($request->end_date) ? $request->end_date :'';
            $logbook->start_odometer  = isset($request->start_odometer) ? $request->start_odometer :'';
            $logbook->end_odometer    = isset($request->end_odometer) ? $request->end_odometer :'';
            $logbook->rate            = isset($request->rate) ? $request->rate :'';
            $logbook->total_distance  = isset($request->total_distance) ? $request->total_distance :'';
            $logbook->total_price     = isset($request->total_price) ? $request->total_price :'';
            $logbook->notes           = isset($request->notes) ? $request->notes :'';
            $logbook->workspace       = getActiveWorkSpace();
            $logbook->created_by      = creatorId();
            $logbook->save();

            event(new CreateLogbook($request,$logbook));
            return redirect()->back()->with('success', __('The log book has been created successfully.'));
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
        if(\Auth::user()->isAbleTo('fleet logbook show'))
        {
            $logbook = Logbook::find($id);

            return view('fleet::logbook.show',compact('logbook'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if(\Auth::user()->isAbleTo('fleet logbook edit'))
        {
            $logbook = Logbook::find($id);
            if (!$logbook) {
                return redirect()->route('logbook.index')->with('error', 'Logbook entry not found.');
            }
            $vehicle = Vehicle::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $rate = ProductService::where('workspace_id', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('type','fleet')->get()->pluck('name','id')->prepend('Select Rate', '');
            $drivers = Driver::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('client')->get();

            return view('fleet::logbook.edit',compact('vehicle','logbook','drivers','rate'));

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
    public function update(Request $request, $id)
    {
        if(\Auth::user()->isAbleTo('fleet logbook edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'driver_name' => 'required|max:120',
                                'vehicle_name' => 'required|max:120',
                                'start_date' => 'required',
                                'end_date' => 'required',
                                'start_odometer' => 'required|max:255',
                                'end_odometer' => 'required|max:255',
                                'rate' => 'required',
                                'total_distance' => 'required',
                                'total_price' => 'required',
                                'notes' => 'required|max:20480',
                            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $logbook                  = Logbook::find($id);
            $logbook->driver_name     = isset($request->driver_name) ? $request->driver_name :'';
            $logbook->vehicle_name    = isset($request->vehicle_name) ? $request->vehicle_name :'';
            $logbook->start_date      = isset($request->start_date) ? $request->start_date :'';
            $logbook->end_date        = isset($request->end_date) ? $request->end_date :'';
            $logbook->start_odometer  = isset($request->start_odometer) ? $request->start_odometer :'';
            $logbook->end_odometer    = isset($request->end_odometer) ? $request->end_odometer :'';
            $logbook->rate            = isset($request->rate) ? $request->rate :'';
            $logbook->total_distance  = isset($request->total_distance) ? $request->total_distance :'';
            $logbook->total_price     = isset($request->total_price) ? $request->total_price :'';
            $logbook->notes           = isset($request->notes) ? $request->notes :'';

            $logbook->save();

            event(new Updatelogbook($request,$logbook));
            return redirect()->back()->with('success', __('The log book details are updated successfully.'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if(\Auth::user()->isAbleTo('fleet logbook delete'))
        {
            $logbook = Logbook::find($id);

            event(new DestroyLogbook($logbook));

            $logbook->delete();

            return redirect()->route('logbook.index')->with('success', 'The log book has been deleted.' );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function Itemrate(Request $request)
    {
        $itemrate = ProductService::where('id', $request->rate)->get(['sale_price']);
        return response()->json($itemrate);

    }
}
