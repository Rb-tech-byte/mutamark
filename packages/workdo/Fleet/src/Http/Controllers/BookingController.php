<?php

namespace Workdo\Fleet\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Workdo\Fleet\Entities\Booking;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\FleetCustomer;
use Workdo\Fleet\Entities\FleetPayment;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Events\CreateBooking;
use Workdo\Fleet\Events\CreateFleetPayment;
use Workdo\Fleet\Events\DestroyBooking;
use Workdo\Fleet\Events\DestroyFleetPayment;
use Workdo\Fleet\Events\UpdateBooking;
use Workdo\Fleet\DataTables\BookingDataTable;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(BookingDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('booking manage')) {
            return $dataTable->render('fleet::booking.index');
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
        if (Auth::user()->isAbleTo('booking create')) {
            $vehicle = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $customer = User::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->where('type', 'client')->pluck('name', 'id')->prepend('Select Customer', '');

            return view('fleet::booking.create', compact('vehicle', 'customer'));
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
        if (Auth::user()->isAbleTo('booking create')) {

            $vehicle = Vehicle::find($request->vehicle_name);
            $validator = Validator::make(
                $request->all(),
                [
                    'vehicle_name'  => 'required',
                    'trip_type'     => 'required',
                    'start_date'    => 'required',
                    'end_date'      => 'required',
                    'start_address' => 'required',
                    'end_address'   => 'required',
                    'status'        => 'required',
                    'notes'         => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }
            $bookings = Booking::create(
                [
                    'customer_name'     => (Auth::user()->type != 'client') ? $request->customer_name : Auth::user()->id,
                    'vehicle_name'      => $request['vehicle_name'],
                    'driver_name'       => $vehicle->driver_name,
                    'trip_type'         => $request['trip_type'],
                    'start_date'        => $request['start_date'],
                    'end_date'          => $request['end_date'],
                    'start_address'     => $request['start_address'],
                    'end_address'       => $request['end_address'],
                    'total_price'       => $request['total_price'] ?? 0,
                    'status'            => $request['status'],
                    'notes'             => $request['notes'],
                    'workspace'         => getActiveWorkSpace(),
                    'created_by'        => creatorId(),
                ]
            );
            event(new CreateBooking($request, $bookings));

            if (!empty(company_setting('New Booking')) && company_setting('New Booking')  == true) {
                $customer = User::where('id', $request->customer_name)->where('workspace_id', '=',  getActiveWorkSpace())->first();

                $User        = Driver::where('id', $vehicle->driver_name)->where('workspace', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'customer_name' => $customer->name,
                    'driver_name'   => $User->name,
                    'total_price'   => $request->total_price,
                ];
                try {
                    $resp = EmailTemplate::sendEmailTemplate('New Booking', [$User->email], $uArr);
                }
                catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('booking.index')->with('success', __('The Booking has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('booking.index')->with('success', __('The Booking has been created successfully..'));
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
        try {
            $bookings = Booking::find($id);
            $payment = FleetPayment::where('booking_id', '=', $bookings->id)->get();

            $payments = FleetPayment::where('booking_id', '=', $id)->get();
            $total_amount                = [];
            $total_amount['pay_amount']  = FleetPayment::paymentSummary($payments);

            $paid_amount = $bookings->total_price - $total_amount['pay_amount'];

            return view('fleet::booking.show', compact('bookings', 'payment', 'total_amount', 'paid_amount'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Booking Not Found'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Booking $booking)
    {
        if (Auth::user()->isAbleTo('booking edit')) {

            $vehicle = Vehicle::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->prepend('Select Vehicle', '');
            $customer = User::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->where('type', 'client')->pluck('name', 'id')->prepend('Select Customer', '');
            $Driver = Driver::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id')->prepend('Select Driver Name', '');
            return view('fleet::booking.edit', compact('booking', 'vehicle', 'customer', 'Driver'));
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
    public function update(Request $request, Booking $booking)
    {
        if (Auth::user()->isAbleTo('booking edit')) {

            $vehicle = Vehicle::find($request->vehicle_name);

            $validator = Validator::make(
                $request->all(),
                [
                    'vehicle_name'  => 'required',
                    'trip_type'     => 'required',
                    'start_date'    => 'required',
                    'end_date'      => 'required',
                    'start_address' => 'required',
                    'end_address'   => 'required',
                    'status'        => 'required',
                    'notes'         => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $booking->customer_name = (Auth::user()->type != 'client') ? $request->customer_name : Auth::user()->id;
            $booking->vehicle_name  = isset($request->vehicle_name) ? $request->vehicle_name : '';
            $booking->driver_name   = isset($vehicle->id) ? $vehicle->id : '';
            $booking->trip_type     = isset($request->trip_type) ? $request->trip_type : '';
            $booking->start_date    = isset($request->start_date) ? $request->start_date : '';
            $booking->end_date      = isset($request->end_date) ? $request->end_date : '';
            $booking->start_address = isset($request->start_address) ? $request->start_address :'';
            $booking->end_address   = isset($request->end_address) ? $request->end_address :'';
            $booking->total_price   = $request->total_price ?? 0;
            $booking->status        = isset($request->status) ? $request->status :'';
            $booking->notes         = isset($request->notes) ? $request->notes :'';
            $booking->workspace     = getActiveWorkSpace();
            $booking->created_by    = creatorId();
            $booking->save();

            event(new UpdateBooking($request, $booking));

            return redirect()->route('booking.index')->with('success', __('The booking details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Booking $booking)
    {
        if (\Auth::user()->isAbleTo('booking delete')) {

            event(new DestroyBooking($booking));

            $booking->delete();

            return redirect()->route('booking.index')->with('success', 'The booking has been deleted.');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function Addpayment($id)
    {
        if (Auth::user()->isAbleTo('payment booking manage')) {

            $bookings = Booking::find($id);
            $payments = FleetPayment::where('booking_id', '=', $id)->get();
            $total_amount                = [];
            $total_amount['pay_amount']       = FleetPayment::paymentSummary($payments);

            $paid_amount = $bookings->total_price - $total_amount['pay_amount'];

            return view('fleet::booking.addpayment', compact('bookings', 'paid_amount'));
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function PaymentStore(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('payment booking manage')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'pay_amount' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $Payment = FleetPayment::create(
                [
                    'pay_amount'    => $request['pay_amount'],
                    'description'   => $request['description'],
                    'booking_id'    => $id,
                    'workspace'     => getActiveWorkSpace(),
                    'created_by'    => creatorId(),
                ]
            );
            event(new CreateFleetPayment($request, $Payment));
            $payments = FleetPayment::where('booking_id', '=', $id)->get();
            $bookings = Booking::find($id);

            $total_amount       = FleetPayment::paymentSummary($payments);

            if (!empty(company_setting('New Booking Payment')) && company_setting('New Booking Payment')  == true) {
                $customer = User::where('id', $bookings->customer_name)->where('workspace_id', '=',  getActiveWorkSpace())->first();


                $uArr = [
                    'customer_name' => $customer->name,
                    'pay_amount'    => $request->pay_amount,
                    'total_price'   => $total_amount,
                ];

                try {
                    $resp = EmailTemplate::sendEmailTemplate('New Booking Payment', [$customer->email], $uArr);
                } catch (\Exception $e) {

                    $resp['error'] = $e->getMessage();
                }
                return redirect()->back()->with('success', __('Payment has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->back()->with('success', __('Payment has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function PaymentDestory($id)
    {
        if (Auth::user()->isAbleTo('payment booking delete')) {
            $payment = FleetPayment::find($id);

            event(new DestroyFleetPayment($payment));

            $payment->delete();

            return redirect()->back()->with('success', __('The payment has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function FleetSettingsStore(Request $request)
    {
        if($request->has('is_enable'))
        {
            $validator = Validator::make($request->all(), [
                'api_key' => 'required|string',
            ]);
            if($validator->fails()){
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
        }
        $getActiveWorkSpace = getActiveWorkSpace();
        $creatorId = creatorId();
        if($request->has('is_enable'))
        {
            $post = $request->all();

            foreach ($post as $key => $value) {
                $data = [
                    'key' => $key,
                    'workspace' => $getActiveWorkSpace,
                    'created_by' => $creatorId,
                ];


                Setting::updateOrInsert($data, ['value' => $value]);
            }
        }else{
            $data = [
                'key' => 'is_enable',
                'workspace' => $getActiveWorkSpace,
                'created_by' => $creatorId,
            ];
            Setting::updateOrInsert($data, ['value' => 'off']);
        }
        // Settings Cache forget
        comapnySettingCacheForget();
        
        return redirect()->back()->with('success','Fleet API key setting save sucessfully.');
    }

}
