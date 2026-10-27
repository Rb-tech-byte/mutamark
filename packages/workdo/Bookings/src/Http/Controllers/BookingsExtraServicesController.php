<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Workdo\Bookings\Entities\BookingsExtraService;
use Illuminate\Routing\Controller;
use Workdo\Bookings\Events\CreateBookingsExtraService;
use Workdo\Bookings\Events\DestroyBookingsExtraService;
use Workdo\Bookings\Events\UpdateBookingsExtraService;

class BookingsExtraServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->isAbleTo('bookings extra services manage'))
        {
            $extraservices = BookingsExtraService::where('workspace', '=', getActiveWorkSpace())->where('created_by',creatorId())->get();
            return view('bookings::extra-services.index',compact('extraservices'));
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
        if(\Auth::user()->isAbleTo('bookings extra services create'))
        {
            return view('bookings::extra-services.create');
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
        if(\Auth::user()->isAbleTo('bookings extra services create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('bookings-extra-services.index')->with('error', $messages->first());
            }
            $bookingextraservice                  = new BookingsExtraService();
            $bookingextraservice->name            = isset($request->name) ? $request->name :'';
            $bookingextraservice->status          = isset($request->status) ? $request->status :'';
            $bookingextraservice->workspace       = getActiveWorkSpace();
            $bookingextraservice->created_by      = creatorId();
            $bookingextraservice->save();

            event(new CreateBookingsExtraService($request,$bookingextraservice));
            return redirect()->route('bookings-extra-services.index')->with('success', __('The extra service has been created successfully.'));
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
        if (\Auth::user()->isAbleTo('bookings extra services edit'))
        {
            $bookingextraservice = BookingsExtraService::find($id);
            return view('bookings::extra-services.edit',compact('bookingextraservice'));
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
        if (\Auth::user()->isAbleTo('bookings extra services edit')) {
            $rules = [
                'name' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $bookingextraservice             = BookingsExtraService::find($id);
            $bookingextraservice->name       = $request->name;
            $bookingextraservice->status     = $request->status;
            $bookingextraservice->save();

            event(new UpdateBookingsExtraService($request,$bookingextraservice));
            return redirect()->route('bookings-extra-services.index')->with('success', __('The extra service details are updated successfully.'));

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
        if(\Auth::user()->isAbleTo('bookings extra services delete'))
        {
            $bookingextraservice = BookingsExtraService::find($id);
            if(!empty($bookingextraservice))
            {
                event(new DestroyBookingsExtraService($bookingextraservice));
                $bookingextraservice->delete();
                return redirect()->route('bookings-extra-services.index')->with('success', 'The extra service has been deleted.' );
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
