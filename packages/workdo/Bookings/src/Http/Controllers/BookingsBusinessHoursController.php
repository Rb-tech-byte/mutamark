<?php

namespace Workdo\Bookings\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Workdo\Bookings\Entities\BookingsBusinessHours;

class BookingsBusinessHoursController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->isAbleTo('bookings business hours manage'))
        {
            return view('bookings::Business_Hours.index');
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
        return redirect()->back();
        return view('bookings::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $post = $request->all();

        unset($post['_token']);
        foreach ($post as $key => $daydata) {
            $dayname = $key;
            $start = $daydata['start'] ?? "9:30";
            $end = $daydata['end'] ?? "18:00";
            $day_off = $daydata['day_off'] ?? 'off';
            $repeater = json_encode(isset($daydata['repeater']) ? $daydata['repeater'] :'') ;

            BookingsBusinessHours::updateOrCreate(['day_name' => $dayname, 'workspace_id' => getActiveWorkSpace(),'created_by' => creatorId()],['start_time'=> $start,'end_time'=> $end, 'break_hours'=> $repeater ,'day_off' => $day_off]);
        }

        $post = [
            'holiday_setting' => $request->holiday_setting,
        ];

        foreach ($post as $key => $value) {

        $data = [
            'key' => $key,
            'workspace' => getActiveWorkSpace(),
            'created_by' => creatorId(),
        ];
        Setting::updateOrInsert($data, ['value' => $value]);
        }

        // Settings Cache forget
        comapnySettingCacheForget();
        return redirect()->back()->with('success', __('The business hours has been created successfully.'));
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
        return redirect()->back();
        return view('bookings::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return redirect()->back();
    }
}
