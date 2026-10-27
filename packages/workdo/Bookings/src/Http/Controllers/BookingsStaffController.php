<?php

namespace Workdo\Bookings\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Bookings\Entities\BookingsStaff;
use Workdo\Bookings\Events\CreateBookingsStaff;
use Workdo\Bookings\Events\DestroyBookingsStaff;
use Workdo\Bookings\Events\UpdateBookingsStaff;
use Workdo\ProductService\Entities\ProductService;
use Workdo\Bookings\DataTables\BookingsStaffDataTable;

class BookingsStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(BookingsStaffDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('booking staffs manage'))
        {
            return $dataTable->render('bookings::staff.index');
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
        if(\Auth::user()->isAbleTo('booking staffs create'))
        {
            $staffs = User::where('workspace_id', getActiveWorkSpace())->emp()->get()->pluck('name', 'id');
            $services = ProductService::where('workspace_id', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('type','bookings')->get()->pluck('name','id');
            return view('bookings::staff.create',compact('staffs','services'));
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
        if(\Auth::user()->isAbleTo('booking staffs create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'staff' => 'required',
                                   'services' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('booking-staff.index')->with('error', $messages->first());
            }

            $bookingstaffs                = new BookingsStaff();
            $bookingstaffs->staff         = isset($request->staff) ? $request->staff : '';
            $bookingstaffs->service       = implode(",", $request->services);
            $bookingstaffs->workspace     = getActiveWorkSpace();
            $bookingstaffs->created_by    = creatorId();
            $bookingstaffs->save();

            event(new CreateBookingsStaff($request,$bookingstaffs));

            return redirect()->route('booking-staff.index')->with('success', __('The staff has been created successfully.'));
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
        if(\Auth::user()->isAbleTo('booking staffs edit'))
        {
            $bookingstaff = BookingsStaff::where('id', $id)->where('workspace', getActiveWorkSpace())->where('created_by',creatorId())->first();
            $staffs = User::where('workspace_id', getActiveWorkSpace())->emp()->get()->pluck('name', 'id');
            $services = ProductService::where('workspace_id', getActiveWorkSpace())->where('created_by', creatorId())->where('type', 'bookings')->get()->pluck('name', 'id');

            return view('bookings::staff.edit', compact('staffs', 'services','bookingstaff'));
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
        if(\Auth::user()->isAbleTo('booking staffs edit'))
        {
            $bookingstaffs = BookingsStaff::find($id);

            $bookingstaffs->staff = $request->staff;
            $bookingstaffs->service = implode(',', $request->input('services', []));
            $bookingstaffs->save();

            event(new UpdateBookingsStaff($request,$bookingstaffs));
            return redirect()->route('booking-staff.index')->with('success', __('The staff details are updated successfully.'));
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
        if(\Auth::user()->isAbleTo('booking staffs delete'))
        {
            $bookingstaffs = BookingsStaff::find($id);

            if(!empty($bookingstaffs))
            {
                event(new DestroyBookingsStaff($bookingstaffs));
                $bookingstaffs->delete();
                return redirect()->back()->with('success', 'The staff has been deleted.');
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

}
