<?php

namespace Workdo\Fleet\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Booking;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\FuelType;
use Workdo\Fleet\Entities\Insurances;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Entities\FleetCustomer;
use Workdo\Fleet\Entities\VehicleType;
use Workdo\Fleet\Entities\DriverAttechment;
use Workdo\Fleet\Events\CreateVehicle;
use Workdo\Fleet\Events\DestroyVehicle;
use Workdo\Fleet\Events\UpdateVehicle;
use Workdo\Fleet\DataTables\VehicleDataTable;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(VehicleDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('vehicle manage')) {
            $vehicleTypes = VehicleType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $fuelType = FuelType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            return $dataTable->render('fleet::vehicle.index', compact('vehicleTypes', 'fuelType'));

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
        if (Auth::user()->isAbleTo('vehicle create')) {
            $vehicleTypes = VehicleType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle Types', '');
            $fuelType = FuelType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Fuel Type', '');
            $drivers = Driver::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

            if(module_is_active('CustomField')){
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id',getActiveWorkSpace())->where('module', '=', 'Fleet')->where('sub_module','Vehicle')->get();
            }else{
                $customFields = null;
            }
            return view('fleet::vehicle.create', compact('vehicleTypes', 'fuelType', 'drivers','customFields'));
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
        if (Auth::user()->isAbleTo('vehicle create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'          => 'required|max:120',
                    'vehicle_type'  => 'required',
                    'registration_date'  => 'required',
                    'register_ex_date'   => 'required',
                    'fuel_type'     => 'required',
                    'driver_name'   => 'required',
                    'lincense_plate'   => 'required',
                    'vehical_id_num'   => 'required',
                    'model_year'    => 'required',
                    'seat_capacity' => 'required',
                    'rate' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $Vehicle = new Vehicle();
            $Vehicle->name                  = $request->name;
            $Vehicle->vehicle_type          = $request->vehicle_type;
            $Vehicle->registration_date     = $request->registration_date;
            $Vehicle->register_ex_date      = $request->register_ex_date;
            $Vehicle->fuel_type             = $request->fuel_type;
            $Vehicle->driver_name           = $request->driver_name;
            $Vehicle->lincense_plate        = $request->lincense_plate;
            $Vehicle->vehical_id_num        = $request->vehical_id_num;
            $Vehicle->model_year            = $request->model_year;
            $Vehicle->status                = $request->status;
            $Vehicle->seat_capacity         = $request->seat_capacity;
            $Vehicle->rate                  = $request->rate;
            $Vehicle->workspace             = getActiveWorkSpace();
            $Vehicle->created_by            = creatorId();
            $Vehicle->save();
            if(module_is_active('CustomField'))
            {
                \Workdo\CustomField\Entities\CustomField::saveData($Vehicle, $request->customField);
            }
            event(new CreateVehicle($request, $Vehicle));
            $vehicleTypes = VehicleType::find($request->vehicle_type);

            if(!empty(company_setting('New Vehicle')) && company_setting('New Vehicle')  == true)
            {
                $User        = Driver::where('id', $request->driver_name)->where('workspace', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'vehicle_name'=>$request->name,
                    'driver_name'=>$User->name,
                    'vehicle_type'=>$vehicleTypes->name,
                ];

                try
                {
                    $resp = EmailTemplate::sendEmailTemplate('New Vehicle', [$User->email], $uArr);
                }
                catch(\Exception $e)
                {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('vehicle.index')->with('success', __('The vehicle has been created successfully.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('vehicle.index')->with('success', __('The vehicle has been created successfully.'));
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
        if (Auth::user()->isAbleTo('vehicle show')) {
            $vehicle = Vehicle::find($id);
            $insurances = Insurances::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->where('vehicle_name', $vehicle->id)->get();
            $maintenances = Maintenance::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->where('vehicle_name', $vehicle->id)->get();
            $bookings = Booking::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->where('vehicle_name', $vehicle->id)->get();
            $fuelTypes = Fuel::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->where('vehicle_name', $vehicle->id)->get();
            $attechments = DriverAttechment::where('created_by',creatorId())->where('workspace', getActiveWorkSpace())->where('driver_id', $vehicle->driver_name)->get();
            return view('fleet::vehicle.show', compact('vehicle','insurances','maintenances','fuelTypes','bookings','attechments'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Vehicle $vehicle)
    {
        if (Auth::user()->isAbleTo('vehicle edit')) {
            $vehicleTypes = VehicleType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle Types', '');
            $FuelType = FuelType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Fuel Type', '');
            // $DriverName = Driver::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id')->prepend('Select Driver Name', '');
            $drivers = Driver::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('client')->get();
            for ($i = 0; $i <= 17; $i++) {
                $data = date('Y', strtotime('-15 years' . " +$i years"));
                $years[$data] = $data;
            }
            if(module_is_active('CustomField')){
                $vehicle->customField = \Workdo\CustomField\Entities\CustomField::getData($vehicle, 'Fleet','Vehicle');
                $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'Fleet')->where('sub_module','Vehicle')->get();
            }else{
                $customFields = null;
            }
            return view('fleet::vehicle.edit', compact('vehicleTypes', 'FuelType', 'drivers', 'vehicle', 'years','customFields'));
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
    public function update(Request $request, Vehicle $vehicle)
    {
        if (Auth::user()->isAbleTo('vehicle edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'          => 'required|max:120',
                    'vehicle_type'  => 'required',
                    'fuel_type'     => 'required',
                    'registration_date' => 'required',
                    'lincense_plate' => 'required',
                    'vehical_id_num' => 'required',
                    'model_year' => 'required',
                    'driver_name'   => 'required',
                    'seat_capacity' => 'required',
                    'rate' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $vehicle->name              = $request->name;
            $vehicle->vehicle_type      = $request->vehicle_type;
            $vehicle->registration_date = $request->registration_date;
            $vehicle->register_ex_date  = $request->register_ex_date;
            $vehicle->fuel_type         = $request->fuel_type;
            $vehicle->driver_name       = $request->driver_name;
            $vehicle->lincense_plate    = $request->lincense_plate;
            $vehicle->vehical_id_num    = $request->vehical_id_num;
            $vehicle->model_year        = $request->model_year;
            $vehicle->status            = $request->status;
            $vehicle->seat_capacity     = $request->seat_capacity;
            $vehicle->rate              = $request->rate;
            $vehicle->workspace         = getActiveWorkSpace();
            $vehicle->created_by        = creatorId();
            $vehicle->save();
            if(module_is_active('CustomField'))
            {
                \Workdo\CustomField\Entities\CustomField::saveData($vehicle, $request->customField);
            }
            event(new UpdateVehicle($request, $vehicle));

            return redirect()->route('vehicle.index')->with('success', __('The vehicle details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Vehicle $vehicle)
    {
        if (Auth::user()->isAbleTo('vehicle delete')) {
            $Vehicles = Booking::where('vehicle_name', $vehicle->id)->first();
            $Insurances = Insurances::where('vehicle_name', $vehicle->id)->first();
            $Maintenance = Maintenance::where('vehicle_name', $vehicle->id)->first();
            $Fuel = Fuel::where('vehicle_name', $vehicle->id)->first();

            if(module_is_active('CustomField'))
            {
                $customFields = \Workdo\CustomField\Entities\CustomField::where('module','Fleet')->where('sub_module','Vehicle')->get();
                foreach($customFields as $customField)
                {
                    $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $vehicle->id)->where('field_id',$customField->id)->first();
                    if(!empty($value)){
                        $value->delete();
                    }
                }
            }

            if (!empty($Vehicles || $Insurances  || $Maintenance || $Fuel)) {
                return redirect()->back()->with('error', __('this vehicle is already use so please transfer or delete this vehicle related data.'));
            }
            event(new DestroyVehicle($vehicle));

            $vehicle->delete();

            return redirect()->route('vehicle.index')->with('success', 'The vehicle has been deleted.');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function vehicleAttechment(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);
        $file_name = time() . "_" . $request->file->getClientOriginalName();

        $upload = upload_file($request, 'file', $file_name, 'driver_attachment', []);

        $fileSizeInBytes = \File::size($upload['url']);
        $fileSizeInKB = round($fileSizeInBytes / 1024, 2);

        if ($fileSizeInKB < 1024) {
            $fileSizeFormatted = $fileSizeInKB . " KB";
        } else {
            $fileSizeInMB = round($fileSizeInKB / 1024, 2);
            $fileSizeFormatted = $fileSizeInMB . " MB";
        }

        if ($upload['flag'] == 1) {
            $file    = DriverAttechment::create(
                [
                    'driver_id' => $vehicle->driver_name,
                    'file_name' => $file_name,
                    'file_path' => $upload['url'],
                    'file_status' => 0,
                    'file_size' => $fileSizeFormatted,
                    'workspace' => getActiveWorkSpace(),
                    'created_by'=> creatorId(),
                ]
            );

            $return               = [];
            $return['is_success'] = true;

            return response()->json($return);
        } else {

            return response()->json(
                [
                    'is_success' => false,
                    'error' => $upload['msg'],
                ],
                401
            );
        }
    }

    public function vehicleAttechmentDestroy($id)
    {
        $file = DriverAttechment::find($id);

        if (!empty($file->file_path)) {
            delete_file($file->file_path);
        }
        $file->delete();
        return redirect()->back()->with('success', __('The file has been deleted.'));
    }

}
