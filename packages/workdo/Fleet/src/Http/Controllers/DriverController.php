<?php

namespace Workdo\Fleet\Http\Controllers;

use App\Models\User;
use Google\Service\DriveActivity\Drive;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\License;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Events\CreateDriver;
use Workdo\Fleet\Events\DestroyDriver;
use Workdo\Fleet\Events\UpdateDriver;
use Workdo\Fleet\Entities\DriverAttechment;
use App\Models\Role;
use Workdo\Fleet\DataTables\DriverDataTable;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(DriverDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('driver manage')) {

            return $dataTable->render('fleet::driver.index');

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
        if (Auth::user()->isAbleTo('driver create')) {

            $lincese_type        = License::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            if (module_is_active('CustomField')) {
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'Fleet')->where('sub_module', 'Driver')->get();
            } else {
                $customFields = null;
            }

            $driver = Driver::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();

            if (!empty($driver) && is_array($driver)) {
                $staffs = User::where('workspace_id', getActiveWorkSpace())->where('type', 'staff')->whereNotIn('id', $driver)->get()->pluck('name', 'id');
            } else {
                $staffs = User::where('workspace_id', getActiveWorkSpace())->where('type', 'staff')->get()->pluck('name', 'id');
            }

            return view('fleet::driver.create', compact('lincese_type', 'customFields','staffs'));
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
        $canUse=  PlanCheck('User',Auth::user()->id);
        if($canUse == false)
        {
            return redirect()->back()->with('error','You have maxed out the total number of User allowed on your current plan');
        }

        $roleDriver = Role::where('name', 'driver')->where('guard_name', 'web')->where('created_by', creatorId())->firstOrFail();
        $roleStaff = Role::where('name', 'staff')->where('guard_name', 'web')->where('created_by', creatorId())->firstOrFail();

        if (Auth::user()->isAbleTo('driver create')) {
            $validator = \Validator::make($request->all(), [
                'select_driver_type' => 'required',
                'phone' => 'required',
                'lincese_number' => 'required',
                'lincese_type' => 'required',
                'expiry_date' => 'required',
                'join_date' => 'required',
                'address' => 'required|max:255',
                'dob' => 'required',
                'Working_time' => 'required',
                'driver_status' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            if($request->select_driver_type == "staff")
            {
                $user = User::where('id',$request->staff_id)->first();
            }
            else if($request->select_driver_type == "contractor")
            {
                if($request->user_id)
                {
                    $user = User::where('id', $request->user_id)->first();
                }
                else {
                    $roles_contractor = Role::where('name', 'contractor')->where('guard_name', 'web')->where('created_by', creatorId())->first();
                    $userpassword                = $request->input('password');
                    $user['name']               = isset($request->name) ?  $request->name : '';
                    $user['email']              = $request->email;
                    $user['password']           = isset($userpassword) ? $userpassword : '';
                    $user['email_verified_at']  = date('Y-m-d h:i:s');
                    $user['lang']               = 'en';
                    $user['type']               = $roles_contractor->name;
                    $user['created_by']         = \Auth::user()->id;
                    $user['workspace_id']       = getActiveWorkSpace();
                    $user['active_workspace']   = getActiveWorkSpace();
                    $user = User::create($user);
                    $user->syncRoles([$roles_contractor->id]);
                }

            }

            $driver = new Driver();
            $driver->user_id                = isset($user->id) ? $user->id : 0;
            $driver->select_driver_type     = !empty($request->select_driver_type) ? $request->select_driver_type : null;
            $driver->name                   = !empty($request->name) ? $request->name : $user->name;
            $driver->email                  = !empty($request->email) ? $request->email : $user->email;
            $driver->password               = !empty($user->password) ? $user->password : null;
            $driver->phone                  = $request->phone;
            $driver->lincese_number         = $request->lincese_number;
            $driver->lincese_type           = $request->lincese_type;
            $driver->expiry_date            = $request->expiry_date;
            $driver->join_date              = $request->join_date;
            $driver->address                = $request->address;
            $driver->dob                    = $request->dob;
            $driver->Working_time           = $request->Working_time;
            $driver->driver_status          = $request->driver_status;
            $driver->workspace              = getActiveWorkSpace();
            $driver->created_by             = creatorId();
            $driver->save();

            if ($request->customer == "staff" && isset($user)) {
                $user->syncRoles([$roleStaff->id, $roleDriver->id]);
            }

            if (module_is_active('CustomField')) {
                \Workdo\CustomField\Entities\CustomField::saveData($driver, $request->customField);
            }

            event(new CreateDriver($request, $driver));

            return redirect()->back()->with('success', __('The driver has been created successfully.'));
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
        if (Auth::user()->isAbleTo('driver show')) {
            $driver = Driver::find($id);
            $driver_attachment = DriverAttechment::where('driver_id', $driver->id)->get();
            return view('fleet::driver.show', compact('driver','driver_attachment'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('driver edit')) {

            $roleDriver = Role::where('name', 'driver')->where('guard_name', 'web')->where('created_by', creatorId())->firstOrFail();
            $roleStaff  = Role::where('name', 'staff')->where('guard_name', 'web')->where('created_by', creatorId())->firstOrFail();
            $user         = User::where('id', $id)->where('workspace_id', getActiveWorkSpace())->first();
            $driver       = Driver::where('user_id', $id)->where('workspace', getActiveWorkSpace())->first();
            $lincese_type = License::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select License Type', '');
            $staffs       = User::where('workspace_id', '=', getActiveWorkSpace())->where('type', 'staff')->get()->pluck('name', 'id');
            if (!empty($driver)) {
                if (module_is_active('CustomField')) {
                    $driver->customField = \Workdo\CustomField\Entities\CustomField::getData($driver, 'Fleet', 'Driver');
                    $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'Fleet')->where('sub_module', 'Driver')->get();
                } else {
                    $customFields = null;
                }
                return view('fleet::driver.edit', compact('user', 'driver', 'lincese_type', 'customFields','staffs','roleStaff','roleDriver'));
            }
                return view('fleet::driver.edit', compact('user', 'driver', 'lincese_type','staffs'));
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
    public function update(Request $request, Driver $driver)
    {
        if (Auth::user()->isAbleTo('driver edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'select_driver_type' => 'required',
                    'phone' => 'required',
                    'lincese_number' => 'required',
                    'lincese_type' => 'required',
                    'expiry_date' => 'required',
                    'join_date' => 'required',
                    'address' => 'required|max:255',
                    'dob' => 'required',
                    'Working_time' => 'required',
                    'driver_status' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $driver = Driver::find($driver->id);

            if ($request->select_driver_type == "staff") {
                $user = User::where('id', $request->staff_id)->first();
                $driver->name                   = $user->name;
                $driver->email                  = $user->email;
                $driver->phone                  = $user->mobile_no;
             } elseif ($request->select_driver_type == "contractor") {

                $old_user = User::where('id', $driver->user_id)->first();

                if($old_user->type == 'staff')
                {
                    $roles_contractor = Role::where('name', 'contractor')->where('guard_name', 'web')->where('created_by', creatorId())->first();
                    $userpassword               = $request->input('password');

                    $user['name']               = isset($request->name) ?  $request->name : '';
                    $user['email']              = $request->email;
                    $user['mobile_no']          = $request->phone;
                    $user['password']           = isset($userpassword) ? $userpassword : '';
                    $user['email_verified_at']  = date('Y-m-d h:i:s');
                    $user['lang']               = 'en';
                    $user['type']               = $roles_contractor->name;
                    $user['created_by']         = creatorId();
                    $user['workspace_id']       = getActiveWorkSpace();
                    $user['active_workspace']   = getActiveWorkSpace();

                    $user = User::create($user);
                    $user->syncRoles([$roles_contractor->id]);
                }
                else
                {
                    $user = User::where('id', $driver->user_id)->first();
                    $user->update(
                    [
                        'name' =>  !empty($request->name) ? $request->name : $user->name,
                        'email' => !empty($request->email) ? $request->email : $user->email,
                        'mobile_no' => !empty($request->phone) ? $request->phone : $user->phone,
                    ]);

                }
                $driver->name   = !empty($request->name) ? $request->name : $user->name;
                $driver->email  = !empty($request->email) ? $request->email : $user->email;
                $driver->phone  = !empty($request->phone) ? $request->phone : $user->phone;
            }

            $driver->user_id                = isset($user->id) ? $user->id : 0;
            $driver->select_driver_type     = !empty($request->select_driver_type) ? $request->select_driver_type : null;
            $driver->lincese_number         = $request->lincese_number;
            $driver->lincese_type           = $request->lincese_type;
            $driver->expiry_date            = $request->expiry_date;
            $driver->join_date              = $request->join_date;
            $driver->address                = $request->address;
            $driver->dob                    = $request->dob;
            $driver->Working_time           = $request->Working_time;
            $driver->driver_status          = $request->driver_status;
            $driver->workspace              = getActiveWorkSpace();
            $driver->created_by             = creatorId();
            $driver->save();

            if (module_is_active('CustomField')) {
                \Workdo\CustomField\Entities\CustomField::saveData($driver, $request->customField);
            }

            event(new UpdateDriver($request, $driver));

            return redirect()->back()->with('success', __('The driver details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('driver delete')) {
            $driver = Driver::find($id);
            event(new DestroyDriver($driver));

            if (module_is_active('CustomField')) {
                $customFields = \Workdo\CustomField\Entities\CustomField::where('module', 'Fleet')->where('sub_module', 'Driver')->get();
                foreach ($customFields as $customField) {
                    $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $driver->id)->where('field_id', $customField->id)->first();
                    if (!empty($value)) {
                        $value->delete();
                    }
                }
            }
            if($driver)
            {
                $drivers     = Vehicle::where('driver_name', $driver->user_id)->first();
                $Fuel        = Fuel::where('driver_name', $driver->user_id)->first();
                if (!empty($drivers || $Fuel)) {
                    return redirect()->back()->with('error', __('this driver is already use so please transfer or delete this driver related data.'));
                }

                $driver->delete();
            }


            return redirect()->back()->with('success', 'The driver has been deleted.');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function grid()
    {
        if (\Auth::user()->isAbleTo('driver manage')) {
            $drivers = Driver::where('workspace', getActiveWorkSpace())->with('client');
            $drivers = $drivers->paginate(11);
            return view('fleet::driver.grid', compact('drivers'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function getUser(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user) {
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'mobile_no' => $user->mobile_no,
            ];
            return response()->json($userData);
        } else {
            return response()->json(['error' => 'User not found']);
        }
    }

    public function driverAttechment(Request $request, $id)
    {
        $driver = driver::find($id);
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
            $file      = DriverAttechment::create(
                [
                    'driver_id' => $driver->id,
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

    public function driverAttechmentDestroy($id)
    {
        $file = DriverAttechment::find($id);

        if (!empty($file->file_path)) {
            delete_file($file->file_path);
        }
        $file->delete();
        return redirect()->back()->with('success', __('The file has been deleted..'));
    }

    public function driverstatus($id,$status)
    {
        $attechment = DriverAttechment::find($id);

        if($status == 1)
        {
            $attechment->file_status = 1;

            $attechment->save();

            return redirect()->back()->with('success', __('Attechment Verify Successfully.'));
        }
        elseif($status == 2)
        {
            $attechment->file_status = 2;

            $attechment->save();

            return redirect()->back()->with('error', __('Attechment Unverified.'));
        }
    }

}
