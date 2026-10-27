<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Fleet\Entities\Insurances;
use Workdo\Fleet\Entities\InsuranceBookings;
use Workdo\Fleet\Events\CreateInsuranceBookings;
use Workdo\Fleet\Events\UpdateInsuranceBookings;
use Workdo\Fleet\Events\DestroyInsuranceBookings;


class InsuranceBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('fleet::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($id)
    {
        $insurances      = Insurances::find($id);
        return view('fleet::insurancebooking.create',compact('insurances'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (\Auth::user()->isAbleTo('fleet insurance booking create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $insurancebookings                  = new InsuranceBookings;
            $insurancebookings->insurance_id    = $request->insurances_id;
            $insurancebookings->start_date      = $request->start_date;
            $insurancebookings->end_date        = $request->end_date;
            $insurancebookings->amount          = $request->amount;
            $insurancebookings->workspace       = getActiveWorkSpace();
            $insurancebookings->created_by      = creatorId();
            $insurancebookings->save();

            event(new CreateInsuranceBookings($request,$insurancebookings));

            return redirect()->back()->with('success', __('The insurance booking has been created successfully.'));
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
        return view('fleet::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (\Auth::user()->isAbleTo('fleet insurance booking edit')) {
            $insurancebookings  = InsuranceBookings::find($id);

            return view('fleet::insurancebooking.edit',compact('insurancebookings'));
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
        if (\Auth::user()->isAbleTo('fleet insurance booking edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $insurancebookings                  = InsuranceBookings::find($id);
            $insurancebookings->start_date      = $request->start_date;
            $insurancebookings->end_date        = $request->end_date;
            $insurancebookings->amount          = $request->amount;
            $insurancebookings->workspace       = getActiveWorkSpace();
            $insurancebookings->created_by      = creatorId();
            $insurancebookings->save();

            event(new UpdateInsuranceBookings($request,$insurancebookings));

            return redirect()->back()->with('success', __('The insurance booking details are updated successfully.'));
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
        if(\Auth::user()->isAbleTo('fleet insurance booking delete'))
        {
            $insurances = InsuranceBookings::find($id);

            event(new DestroyInsuranceBookings($insurances));

            $insurances->delete();

            return redirect()->back()->with('success', 'The insurance booking has been deleted.' );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
