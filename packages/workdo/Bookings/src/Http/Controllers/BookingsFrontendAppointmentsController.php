<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use App\Models\User;
use App\Models\WorkSpace;
use Workdo\Bookings\Entities\BookingsPackage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Hrm\Entities\Holiday;
use Workdo\Bookings\Entities\BookingsAppointment;
use Workdo\Bookings\Entities\BookingsCustomer;
use Workdo\Bookings\Events\CreateBookingsAppointment;
use Workdo\ProductService\Entities\ProductService;

class BookingsFrontendAppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index($slug, $lang = null)
    {
        try {
            $slug = $slug;
            $workspace        = WorkSpace::where('slug', $slug)->first();
            $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);
            if ($lang == '') {
                $lang = !empty($company_settings['defult_language']) ? $company_settings['defult_language'] : 'en';
            }
            \App::setLocale($lang);
            $service = ProductService::where('workspace_id', '=', $workspace->id)->where('created_by',$workspace->created_by)->where('type','bookings')->get()->pluck('name','id');
            $customers = BookingsCustomer::where('workspace', '=', $workspace->id)->where('created_by',$workspace->created_by)->with('client')->get();
            $staff = User::where('workspace_id', $workspace->id)->where('created_by',$workspace->created_by)->emp()->get()->pluck('name', 'id');
            return view('bookings::frontend.index', compact('workspace', 'slug', 'lang','service','customers','staff'));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('bookings::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $slug)
    {
        $validator = \Validator::make(
            $request->all(), [
                                'date' => 'required',
                                'service' => 'required',
                                'package' => 'required',
                                'staff' => 'required',
                            //    'client_id' => 'required',
                                'start_time' => 'required',
                                'end_time' => 'required',
                                'your_country' => 'required',
                                'your_state' => 'required',
                                'your_city' => 'required',
                                'your_address' => 'required',
                                'your_zip_code' => 'required',
                                'our_country' => 'required',
                                'our_state' => 'required',
                                'our_city' => 'required',
                                'our_zip_code' => 'required',
                                'name' => 'required',
                                'email' => 'required',
                                'number' => 'required',
                                'customer' => 'required',
                            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $workspace          = WorkSpace::where('slug', $slug)->first();
        if($request->payment_option == 'Offline')
        {
            $bookingscustomer = BookingsCustomer::where('email', $request->email)->first();
            if ($bookingscustomer) {

                $bookingscustomer->name        = isset($request->name) ? $request->name : $bookingscustomer->name;
                $bookingscustomer->client_id   = isset($request->client_name) ? $request->client_name : $bookingscustomer->client_id;
                $bookingscustomer->number      = isset($request->number) ? $request->number : $bookingscustomer->number;
                $bookingscustomer->customer    = isset($request->customer) ? $request->customer : $bookingscustomer->customer;
                $bookingscustomer->workspace   = $workspace->id;
                $bookingscustomer->created_by  = $workspace->created_by;
                $bookingscustomer->save();
            } else {

                $bookingscustomer = new BookingsCustomer();
                $bookingscustomer->name        = isset($request->name) ? $request->name : '';
                $bookingscustomer->client_id   = isset($request->client_name) ? $request->client_name : '';
                $bookingscustomer->number      = isset($request->number) ? $request->number : '';
                $bookingscustomer->email       = isset($request->email) ? $request->email : '';
                $bookingscustomer->customer    = isset($request->customer) ? $request->customer : '';
                $bookingscustomer->workspace   = $workspace->id;
                $bookingscustomer->created_by  = $workspace->created_by;
                $bookingscustomer->save();
            }

            $bookingsappointment                  = new BookingsAppointment();
            $bookingsappointment->appointment_id  = $this->AppointmentNumber($slug);
            $bookingsappointment->date            = isset($request->date) ? $request->date :'-';
            $bookingsappointment->service         = isset($request->service) ? $request->service :'-';
            $bookingsappointment->package         = isset($request->package) ? $request->package :'-';
            $bookingsappointment->staff           = isset($request->staff) ? $request->staff :'-';
            $bookingsappointment->client_id       = isset($request->client_id) ? $request->client_id : $bookingscustomer->id ;
            $bookingsappointment->start_time      = isset($request->start_time) ? $request->start_time :'-';
            $bookingsappointment->end_time        = isset($request->end_time) ? $request->end_time :'-';
            $bookingsappointment->your_country    = isset($request->your_country) ? $request->your_country :'-';
            $bookingsappointment->your_state      = isset($request->your_state) ? $request->your_state :'-';
            $bookingsappointment->your_city       = isset($request->your_city) ? $request->your_city :'-';
            $bookingsappointment->your_address    = isset($request->your_address) ? $request->your_address :'-';
            $bookingsappointment->your_zip_code   = isset($request->your_zip_code) ? $request->your_zip_code :'-';
            $bookingsappointment->our_country     = isset($request->our_country) ? $request->our_country :'-';
            $bookingsappointment->our_state       = isset($request->our_state) ? $request->our_state :'-';
            $bookingsappointment->our_city        = isset($request->our_city) ? $request->our_city :'-';
            $bookingsappointment->our_zip_code    = isset($request->our_zip_code) ? $request->our_zip_code :'-';
            $bookingsappointment->payment         = 'Manually';
            $bookingsappointment->stage_id        = 'Pending';
            $bookingsappointment->workspace       = $workspace->id;
            $bookingsappointment->created_by      = $workspace->created_by;
            $bookingsappointment->save();

            event(new CreateBookingsAppointment($request,$bookingsappointment));

            $msg =  __('Appointment Successfully Created.');
            return redirect()->back()->with('msg', $msg);

        }

    }

    function AppointmentNumber($slug)
    {
        $workspace                      = WorkSpace::where('slug', $slug)->first();
        $workspace = $workspace->id;
        $appointment_id = BookingsAppointment::where('workspace', $workspace)->max('appointment_id');
        if ($appointment_id == null) {
            return 1;
        } else {
            return $appointment_id + 1;
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

    public function bookingsSearchAppointment(Request $request, $id, $slug)
    {
        $returnHTML   = '';
        $formattedSelectedDate = $request->date;
        $service      = $request->service;
        $staff        = $request->staff;
        $package      = $request->package;
        $workspace    = WorkSpace::where('slug', $slug)->first();
        $settings     = getCompanyAllSetting($workspace->created_by, $workspace->id);
        $serivceID    = BookingsPackage::where('item', $service)->where('id',$package)->where('workspace',$id)->first();
        $service      = $serivceID->id;

        if (isset($settings['holiday_setting']) ? $settings['holiday_setting'] == 'on' : '') {
            $holidays = Holiday::where('workspace', $workspace->id)
            ->where('start_date', '<=', $formattedSelectedDate)
            ->where('end_date', '>=', $formattedSelectedDate)
            ->exists();

            if ($holidays) {
                $response = [
                    'is_success' => false,
                    'message' => __('Selected date is a holiday. Please choose another date.'),
                    'html' => null,
                ];
                return $response;
            }
        }

            $appointment = null;

            if($request->appointment_id)
            {
                $appointment = BookingsAppointment::find($request->appointment_id);

            }
            $timeSlots = BookingsAppointment::timeSlot($service, $request->date,$appointment, $slug);

            $html = view('bookings::frontend.append', compact('service', 'staff', 'package', 'serivceID', 'timeSlots','service','appointment', 'formattedSelectedDate','slug','workspace'))->render();

            return response()->json([
                'html' => $html,
                'timeSlots' => $timeSlots,
                'result' => 'success'
            ]);
        }

    public function convertToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);
        return ($hours * 60) + $minutes;
    }

}
