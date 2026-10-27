<?php

namespace Workdo\Fleet\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Booking;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\FleetCustomer;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\Fleet\Entities\Vehicle;

class FleetController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }
    public function index()
    {
        if (Auth::user()->isAbleTo('fleet dashboard manage'))
        {
            if(Auth::user()->type == "staff")
            {

                $status = Maintenance::where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->where('service_for', Auth::user()->id)->get()->count();

                $pending['pending'] = Maintenance::where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->where('service_for',Auth::user()->id)->get()->count();
                $accept['accept'] = Maintenance::where('created_by',creatorId())->where('workspace',getActiveWorkSpace())->where('service_for',Auth::user()->id)->get()->count();
                $decline['decline'] = Maintenance::where('created_by',creatorId())->where('workspace',getActiveWorkSpace())->where('service_for',Auth::user()->id)->get()->count();

                // Top 5 maintenance
                $maintenances = Maintenance::orderBy('created_at', 'Desc')->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->where('service_for', Auth::user()->id)->limit(5)->get();

                return view('fleet::dashboard.employee',compact('maintenances','pending','accept','decline','status'));
            }
            elseif(Auth::user()->type == "driver")
            {

                // top 5 vehicle
                $vehicless = Vehicle::orderBy('created_at', 'Desc')->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->where('driver_name', Auth::user()->id)->limit(5)->get();

                $vehicles['vehicle'] = Vehicle::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->where('driver_name', Auth::user()->id)->get()->count();
                $fuel['amount'] = Fuel::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->where('driver_name', Auth::user()->id)->get()->sum('amount');
                $fuel['quantity'] = Fuel::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->where('driver_name', Auth::user()->id)->get()->sum('quantity');

                // Top 5 fuel
                $fuels = Fuel::orderBy('created_at', 'Desc')->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->where('driver_name', Auth::user()->id)->limit(5)->get();

                return view('fleet::dashboard.driver',compact('fuels','vehicles','fuel','vehicless'));
            }
            elseif(Auth::user()->type == "client")
            {

                // status
                $curr_start = Booking::where('created_by',creatorId())->where('workspace',getActiveWorkSpace())
                ->where('status','Yet to start')
                ->where('customer_name', Auth::user()->id)
                ->count();

                $curr_ongoing = Booking::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())
                ->where('status','OnGoing')
                ->where('customer_name', Auth::user()->id)
                ->count();
                $curr_complete = Booking::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())
                ->where('status','Completed')
                ->where('customer_name', Auth::user()->id)
                ->count();
                $curr_cancelled = Booking::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())
                ->where('status','Cancelled')
                ->where('customer_name', Auth::user()->id)
                ->count();


                // Top 5 Bookings
                $bookings = Booking::orderBy('created_at', 'Desc')->where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->where('customer_name', Auth::user()->id)->limit(5)->get();

                return view('fleet::dashboard.customer',compact('bookings','curr_start','curr_ongoing','curr_complete','curr_cancelled'));
            }
            else
            {
                // User count
                $Customers['customer'] = FleetCustomer::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get()->count();
                $Drivers['driver'] = Driver::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get()->count();
                $Vehicle['vehicle'] = Vehicle::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get()->count();
                $Booking['booking'] = Booking::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->get()->count();

                // Top 5 Bookings
                $bookings = Booking::orderBy('created_at', 'Desc')->where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->with('BookingUser','vehicle')->limit(5)->get();
                // Booking Status
                $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
                $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();

                $curr_month  = Booking::where('created_by', '=', creatorId())
                ->where('workspace',getActiveWorkSpace())->whereBetween('start_date',[$fromDate,$tillDate])
                ->count();

                $curr_start = Booking::where('created_by', creatorId())
                ->where('workspace',getActiveWorkSpace())->where('status','Yet to start')
                ->whereBetween('start_date',[$fromDate,$tillDate])
                ->count();

                $curr_ongoing = Booking::where('created_by', '=', creatorId())
                ->where('workspace',getActiveWorkSpace())->where('status','OnGoing')
                ->whereBetween('start_date', [$fromDate,$tillDate])
                ->count();
                $curr_complete = Booking::where('created_by', '=', creatorId())
                ->where('workspace',getActiveWorkSpace())->where('status','Completed')
                ->whereBetween('start_date', [$fromDate,$tillDate])
                ->count();
                $curr_cancelled = Booking::where('created_by', '=', creatorId())
                ->where('workspace',getActiveWorkSpace())->where('status','Cancelled')
                ->whereBetween('start_date', [$fromDate,$tillDate])
                ->count();

                  // Maintenance & Fule

                  $transdate    = date('Y-m-d', time());
                  $m            = date("m");
                  $de           = date("d");
                  $y            = date("Y");
                  $format       = 'Y-m-d';
                  $user         = \Auth::user();
                  $chartData    = Maintenance::getOrderChart(['duration' => 'week']);

                  $arrTemp = [];
                  for($i = 0; $i <= 7 - 1; $i++)
                  {
                      $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                      $arrTemp['date'][]    = __(date('d-M', strtotime($date)));
                      $arrTemp['maintenance'][] = Maintenance::whereDate('maintenance_date',$date)->where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->count();
                      $arrTemp['fuel'][] = Fuel::whereDate('fill_date',$date)->where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->count();
                      $arrTemp['booking'][] = Booking::whereDate('created_at',$date)->where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->count();
                }
                  $chartData = $arrTemp;

                return view('fleet::dashboard.index',compact('Booking','Vehicle','Customers','Drivers','chartData','bookings','curr_start','curr_month','curr_ongoing','curr_complete','curr_cancelled'));
            }

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
        return view('fleet::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
