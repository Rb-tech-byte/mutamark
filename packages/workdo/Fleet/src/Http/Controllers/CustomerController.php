<?php

namespace Workdo\Fleet\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Fleet\Entities\Booking;
use Workdo\Fleet\Entities\FleetCustomer;
use Workdo\Fleet\Events\CreateFleetCustomer;
use Workdo\Fleet\Events\DestroyFleetCustomer;
use Workdo\Fleet\Events\UpdateFleetCustomer;
use App\Models\Role;
use Workdo\Fleet\DataTables\CustomerDataTable;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(CustomerDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('fleet customer manage')) {
            return $dataTable->render('fleet::customer.index');
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
        if (Auth::user()->isAbleTo('fleet customer create')) {
            if (module_is_active('CustomField')) {
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'Fleet')->where('sub_module', 'Customer')->get();
            } else {
                $customFields = null;
            }
            $client = User::where('workspace_id', '=', getActiveWorkSpace())->where('type', 'Client')->get()->pluck('name', 'id');
            return view('fleet::customer.create', compact('customFields','client'));
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
        if (Auth::user()->isAbleTo('fleet customer create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                    'address' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('fleet_customer.index')->with('error', $messages->first());
            }
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }
            if (isset($request->user_id)) {
                $user = User::find($request->user_id);

                if (empty($user)) {
                    return redirect()->back()->with('error', __('Something went wrong please try again.'));
                }
                if ($user->name != $request->name) {
                    $user->name = $request->name;
                    $user->save();
                }
            }

                $customers                 = new FleetCustomer();
                $customers->user_id        = isset($user->id) ? $user->id : 0;
                $customers->customer       = !empty($request->customer) ? $request->customer : '';
                $customers->name           = !empty($request->name) ? $request->name : '';
                $customers->client_id      = !empty($request->client_id) ? $request->client_id : '';
                $customers->email          = !empty($request->email) ? $request->email : '';
                $customers->phone          = $request->phone;
                $customers->address        = $request->address;
                $customers->workspace      = getActiveWorkSpace();
                $customers->created_by     = creatorId();
                $customers->save();

            if (module_is_active('CustomField')) {
                \Workdo\CustomField\Entities\CustomField::saveData($customers, $request->customField);
            }
            event(new CreateFleetCustomer($request, $customers));

            return redirect()->back()->with('success', __('The customer has been created successfully.'));
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
        return redirect()->back()->with('error', __('Permission denied.'));
        return view('fleet::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('fleet customer edit')) {
            $customer    = FleetCustomer::where('id', $id)->where('workspace', getActiveWorkSpace())->first();
            $user        = User::where('id', $id)->where('workspace_id', getActiveWorkSpace())->first();
            $client      = User::where('workspace_id', '=', getActiveWorkSpace())->where('type', 'Client')->get()->pluck('name', 'id');

            if(!empty($customer)){
                if (module_is_active('CustomField')) {
                    $customer->customField = \Workdo\CustomField\Entities\CustomField::getData($customer, 'Fleet', 'Customer');
                    $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'Fleet')->where('sub_module', 'Customer')->get();
                } else {
                    $customFields = null;
                }
                return view('fleet::customer.edit', compact('customer','customFields','user','client'));
            }
            return view('fleet::customer.edit', compact('customer', 'user', 'client'));

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
    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('fleet customer edit')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                    'address' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $customer                 = FleetCustomer::find($id);
            $customer->user_id        = isset($user->id) ? $user->id : 0;
            $customer->customer       = !empty($request->customer) ? $request->customer : '';
            $customer->name           = !empty($request->name) ? $request->name : '';
            $customer->client_id      = !empty($request->client_id) ? $request->client_id : '';
            $customer->email          = !empty($request->email) ? $request->email : '';
            $customer->phone          = $request->phone;
            $customer->address        = $request->address;
            $customer->workspace      = getActiveWorkSpace();
            $customer->created_by     = creatorId();
            $customer->update();

            if (module_is_active('CustomField')) {
                \Workdo\CustomField\Entities\CustomField::saveData($customer, $request->customField);
            }
            event(new UpdateFleetCustomer($request, $customer));

            return redirect()->back()->with('success', __('The customer are updated successfully.'));
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
        if (Auth::user()->isAbleTo('fleet customer delete')) {
        $customer     = FleetCustomer::where('id', $id)->where('workspace', getActiveWorkSpace())->first();

        event(new DestroyFleetCustomer($customer));

        $customer->delete();


        return redirect()->route('fleet_customer.index')->with('success', 'The customer has been deleted.');

        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function  getUser(Request $request)
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

}
