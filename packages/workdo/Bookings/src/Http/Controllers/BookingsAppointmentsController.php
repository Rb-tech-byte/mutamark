<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Workdo\Hrm\Entities\Holiday;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Workdo\Bookings\Entities\BookingsPackage;
use Workdo\Bookings\Entities\BookingsCustomStatus;
use Workdo\ProductService\Entities\Tax;
use Workdo\Bookings\Entities\BookingsAppointment;
use Workdo\ProductService\Entities\ProductService;
use Workdo\Bookings\Entities\BookingsCustomer;
use Workdo\Bookings\Events\CreateBookingsAppointment;
use Workdo\Bookings\Events\DestroyBookingsAppointment;
use Workdo\Bookings\Events\UpdateBookingsAppointment;
use Workdo\Bookings\DataTables\BookingsAppointmentDataTable;

class BookingsAppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(BookingsAppointmentDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('bookings appointments manage'))
        {
            return $dataTable->render('bookings::Appointments.index');
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
        if(\Auth::user()->isAbleTo('bookings appointments create'))
        {
            $customers = BookingsCustomer::where('workspace', '=', getActiveWorkSpace())->where('created_by',creatorId())->with('client')->get();
            $staff = User::where('workspace_id', getActiveWorkSpace())->emp()->get()->pluck('name', 'id');
            $service = ProductService::where('workspace_id', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('type','bookings')->get()->pluck('name','id');

            return view('bookings::Appointments.create', compact('customers','staff','service'));
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
        if(\Auth::user()->isAbleTo('bookings appointments create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                        'date' => 'required',
                        'service' => 'required',
                        'package' => 'required',
                        'staff' => 'required',
                        'client_id' => 'required',
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
                    ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $bookingsappointment                  = new BookingsAppointment();
            $bookingsappointment->appointment_id  = $this->AppointmentNumber();
            $bookingsappointment->date            = isset($request->date) ? $request->date :'';
            $bookingsappointment->service         = isset($request->service) ? $request->service :'';
            $bookingsappointment->package         = isset($request->package) ? $request->package :'';
            $bookingsappointment->staff           = isset($request->staff) ? $request->staff :'';
            $bookingsappointment->client_id       = isset($request->client_id) ? $request->client_id :'';
            $bookingsappointment->start_time      = isset($request->start_time) ? $request->start_time :'';
            $bookingsappointment->end_time        = isset($request->end_time) ? $request->end_time :'';
            $bookingsappointment->your_country    = isset($request->your_country) ? $request->your_country :'';
            $bookingsappointment->your_state      = isset($request->your_state) ? $request->your_state :'';
            $bookingsappointment->your_city       = isset($request->your_city) ? $request->your_city :'';
            $bookingsappointment->your_address    = isset($request->your_address) ? $request->your_address :'';
            $bookingsappointment->your_zip_code   = isset($request->your_zip_code) ? $request->your_zip_code :'';
            $bookingsappointment->our_country     = isset($request->our_country) ? $request->our_country :'';
            $bookingsappointment->our_state       = isset($request->our_state) ? $request->our_state :'';
            $bookingsappointment->our_city        = isset($request->our_city) ? $request->our_city :'';
            $bookingsappointment->our_zip_code    = isset($request->our_zip_code) ? $request->our_zip_code :'';
            $bookingsappointment->payment         = 'Manually';
            $bookingsappointment->stage_id        = 'Pending';
            $bookingsappointment->workspace       = getActiveWorkSpace();
            $bookingsappointment->created_by      = creatorId();
            $bookingsappointment->save();

            event(new CreateBookingsAppointment($request,$bookingsappointment));
            return redirect()->back()->with('success', __('The bookings appointment has been created successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    function AppointmentNumber()
    {
        $workspace = getActiveWorkSpace();

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
        if(\Auth::user()->isAbleTo('bookings appointments show'))
        {
            $appointment   = BookingsAppointment::find($id);

            return view('bookings::Appointments.view',compact('appointment'));
        }
        else
        {
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
        if(\Auth::user()->isAbleTo('bookings appointments edit'))
        {
            $bookingsappointment  = BookingsAppointment::where('id', $id)->where('workspace', getActiveWorkSpace())->first();

            $customers = BookingsCustomer::where('workspace', '=', getActiveWorkSpace())->where('created_by',creatorId())->with('client')->get();
            $staff = User::where('workspace_id', getActiveWorkSpace())->emp()->get()->pluck('name', 'id');
            $service = ProductService::where('workspace_id', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('type','bookings')->get()->pluck('name','id');
            $status = BookingsAppointment::$status;

            return view('bookings::Appointments.edit',compact('bookingsappointment','customers','staff','service','status'));
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

        if(\Auth::user()->isAbleTo('bookings appointments edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                    'date' => 'required',
                                    'service' => 'required',
                                    'package' => 'required',
                                    'staff' => 'required',
                                    'client_id' => 'required',
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
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $bookingsappointment                    =  BookingsAppointment::find($id);
            $bookingsappointment['date']            = isset($request->date) ? $request->date :'';
            $bookingsappointment['service']         = isset($request->service) ? $request->service :'';
            $bookingsappointment['package']         = isset($request->package) ? $request->package :'';
            $bookingsappointment['staff']           = isset($request->staff) ? $request->staff :'';
            $bookingsappointment['client_id']       = isset($request->client_id) ? $request->client_id :'';
            $bookingsappointment['start_time']      = isset($request->start_time) ? $request->start_time :'';
            $bookingsappointment['end_time']        = isset($request->end_time) ? $request->end_time :'';
            $bookingsappointment['your_country']    = isset($request->your_country) ? $request->your_country :'';
            $bookingsappointment['your_state']      = isset($request->your_state) ? $request->your_state :'';
            $bookingsappointment['your_city']       = isset($request->your_city) ? $request->your_city :'';
            $bookingsappointment['your_address']    = isset($request->your_address) ? $request->your_address :'';
            $bookingsappointment['your_zip_code']   = isset($request->your_zip_code) ? $request->your_zip_code :'';
            $bookingsappointment['our_country']     = isset($request->our_country) ? $request->our_country :'';
            $bookingsappointment['our_state']       = isset($request->our_state) ? $request->our_state :'';
            $bookingsappointment['our_city']        = isset($request->our_city) ? $request->our_city :'';
            $bookingsappointment['our_zip_code']    = isset($request->our_zip_code) ? $request->our_zip_code :'';
            $bookingsappointment['workspace']       = getActiveWorkSpace();
            $bookingsappointment['created_by']      = creatorId();
            $bookingsappointment->update();

            event(new UpdateBookingsAppointment($request,$bookingsappointment));

            return redirect()->back()->with('success', __('The bookings appointment details are updated successfully.'));
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
        if(\Auth::user()->isAbleTo('bookings appointments delete'))
        {
            $bookingsappointment = BookingsAppointment::find($id);
            if(!empty($bookingsappointment))
            {
                event(new DestroyBookingsAppointment($bookingsappointment));
                $bookingsappointment->delete();
                return redirect()->back()->with('success', 'Bookings Appointment Successfully Deleted.' );
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function bookingsappointmentStatusChange($id)
    {
        $appointment = BookingsAppointment::find($id);
        $status = BookingsAppointment::$status;

        return view('bookings::Appointments.change-status', compact('appointment', 'status'));
    }

    public function bookingsappointmentStatusUpdate(Request $request, $id)
    {
        $appointment = BookingsAppointment::find($id);
        $appointment->stage_id =  $request->status;
        $appointment->save();

        return redirect()->back()->with('success', __('Appointment status change successfully.'));
    }

    public function kanban()
    {
        $appointments = BookingsAppointment::where('workspace', '=', getActiveWorkSpace())->where('created_by',creatorId())->with('appointment_staff')->with('appointment_service')->with('customerData')->get()->groupBy('stage_id')->toArray();
        $stages = BookingsAppointment::$status;

        return view('bookings::Appointments.kanban', compact('stages','appointments'));
    }

    public function stageOrder(Request $request)
    {
        $bookingsappointment               = BookingsAppointment::find($request->booking_id);
        $bookingsappointment['stage_id']   = $request->stage_id;
        $bookingsappointment->save();

        return response()->json(['status'=> 1]);
    }

    public function calendar(Request $request)
    {
        $user = Auth::user();
        $beauty_book     = [];
        $appointments   = BookingsAppointment::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace());

        if (!empty($request->date)) {
            $date_range = explode(' to ', $request->date);
            $beautybooking->where('start_date', '>=', $date_range[0]);
            $beautybooking->where('end_date', '<=', $date_range[1]);
        }

        $appointments = $appointments->get();
        foreach ($appointments as $appointment) {
            $formattedAppointmentNumber = \Workdo\Bookings\Entities\BookingsAppointment::appointmentNumberFormat($appointment->appointment_id);
            $data = [
                'title' => $formattedAppointmentNumber,
                'start' => $appointment->date,
                'end' => $appointment->date,
                'className' => 'event-danger'
            ];
            $beauty_book[] = $data;
        }

        $current_month_booking = BookingsAppointment::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->where('created_by', creatorId())
            ->where('workspace', getActiveWorkSpace())
            ->OrderBy('date', 'ASC')
            ->get();

        return view('bookings::Appointments.calendar',compact('beauty_book','current_month_booking'));
    }

    public function searchAppointment(Request $request)
    {
        $returnHTML = '';
        $formattedSelectedDate = $request->date;
        $service = $request->service;
        $customer = $request->customer;
        $staff = $request->staff;
        $package = $request->package;
        $settings = getCompanyAllSetting();

        $serivceID = BookingsPackage::where('item', $service)->where('id',$package)->first();
        $service = $serivceID->id;


        if ($settings['holiday_setting'] == 'on') {
            $holidays = Holiday::where('workspace',getActiveWorkSpace())
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

        } else {

            $appointment = null;

            if($request->appointment_id)
            {
                $appointment = BookingsAppointment::find($request->appointment_id);
            }
            $timeSlots = BookingsAppointment::timeSlot($service, $request->date,$appointment);

            $html = view('bookings::Appointments.slot', compact('service', 'customer', 'staff', 'package', 'serivceID', 'timeSlots','service','appointment', 'formattedSelectedDate'))->render();

            return response()->json([
                'html' => $html,
                'timeSlots' => $timeSlots,
                'result' => 'success'
            ]);

        }
    }

    public function convertToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);
        return ($hours * 60) + $minutes;
    }


}

