<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Workdo\Bookings\Entities\BookingsCustomer;
use Workdo\Bookings\Events\CreateBookingsCustomer;
use Workdo\Bookings\Events\DestroyBookingsCustomer;
use Workdo\Bookings\Events\UpdateBookingsCustomer;
use Workdo\Bookings\DataTables\BookingsCustomersDataTable;

class BookingsCustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(BookingsCustomersDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('bookingcustomers create'))
        {
            return $dataTable->render('bookings::customers.index');
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
        if(\Auth::user()->isAbleTo('bookingcustomers create'))
        {
            $client = User::where('workspace_id', '=', getActiveWorkSpace())->where('type', 'Client')->get()->pluck('name', 'id');
            return view('bookings::customers.create',compact('client'));
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
        if(\Auth::user()->isAbleTo('bookingcustomers create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'number' => 'required',
                                   'email' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('booking-customers.index')->with('error', $messages->first());
            }
            $bookingscustomer                  = new BookingsCustomer();
            $bookingscustomer->name            = isset($request->name) ? $request->name :'';
            $bookingscustomer->client_id       = isset($request->client_name) ? $request->client_name :'';
            $bookingscustomer->number          = isset($request->number) ? $request->number :'';
            $bookingscustomer->email           = isset($request->email) ? $request->email :'';
            $bookingscustomer->customer        = isset($request->Customer) ? $request->Customer :'';
            $bookingscustomer->workspace       = getActiveWorkSpace();
            $bookingscustomer->created_by      = creatorId();
            $bookingscustomer->save();

            event(new CreateBookingsCustomer($request,$bookingscustomer));
            return redirect()->route('booking-customers.index')->with('success', __('The customer has been created successfully.'));
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
        return redirect()->back();
        return view('bookings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if(\Auth::user()->isAbleTo('bookingcustomers edit'))
        {
            $booking  = BookingsCustomer::where('id', $id)->where('workspace', getActiveWorkSpace())->first();
            $client = User::where('workspace_id', '=', getActiveWorkSpace())->where('type', 'Client')->get()->pluck('name', 'id');

            return view('bookings::customers.edit',compact('booking','client'));
        }
        else
        {
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
        if(\Auth::user()->isAbleTo('bookingcustomers edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'number' => 'required',
                                'email' => 'required',
                                'Customer' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $bookingscustomer                    =  BookingsCustomer::find($id);
            $bookingscustomer['name']            =  isset($request->name) ? $request->name :'';
            $bookingscustomer['client_id']       =  isset($request->client_name) ? $request->client_name :'';
            $bookingscustomer['number']          =  isset($request->number) ? $request->number :'';
            $bookingscustomer['email']           =  isset($request->email) ? $request->email :'';
            $bookingscustomer['customer']        =  isset($request->Customer) ? $request->Customer :'';
            $bookingscustomer['workspace']       =  getActiveWorkSpace();
            $bookingscustomer['created_by']      =  creatorId();
            $bookingscustomer->update();

            event(new UpdateBookingsCustomer($request,$bookingscustomer));

            return redirect()->route('booking-customers.index')->with('success', __('The customer details are updated successfully.'));
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
    public function destroy($id)
    {
        if(\Auth::user()->isAbleTo('bookingcustomers delete'))
        {
            $bookingscustomer = BookingsCustomer::find($id);
            if(!empty($bookingscustomer))
            {
                event(new DestroyBookingsCustomer($bookingscustomer));
                $bookingscustomer->delete();
                return redirect()->route('booking-customers.index')->with('success', 'The customer has been deleted.');
            }
        }
        else
        {
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
