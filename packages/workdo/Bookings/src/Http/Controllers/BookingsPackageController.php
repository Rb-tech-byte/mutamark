<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Bookings\Events\DuplicateBookingsPackage;
use Workdo\ProductService\Entities\ProductService;
use Workdo\Bookings\Entities\BookingsExtraService;
use Workdo\Bookings\Entities\BookingsPackage;
use Workdo\Bookings\Events\CreateBookingsPackage;
use Workdo\Bookings\Events\DestroyBookingsPackage;
use Workdo\Bookings\Events\UpdateBookingsPackage;
use Workdo\Bookings\DataTables\BookingsPackageDataTable;

class BookingsPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(BookingsPackageDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('bookings package manage'))
        {
            return $dataTable->render('bookings::package.index');
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
        if(\Auth::user()->isAbleTo('bookings package create'))
        {
            $services = ProductService::where('type', 'bookings')->where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name','id');
            $extraservices = BookingsExtraService::where('workspace', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('status','=','active')->get()->pluck('name','id');
            $periods = BookingsPackage::$periods;

            return view('bookings::package.create',compact('services','extraservices','periods'));
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

        if(\Auth::user()->isAbleTo('bookings package create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'item' => 'required',
                                   'services' => 'required',
                                   'delivery_time' => 'required',
                                   'delivery_period' => 'required',
                                   'price' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('booking-package.index')->with('error', $messages->first());
            }
            $bookingspackage                    = new BookingsPackage();
            $bookingspackage->name              = isset($request->name) ? $request->name :'';
            $bookingspackage->item              = isset($request->item) ? $request->item :'';
            $bookingspackage->services          = implode(",", $request->services);
            $bookingspackage->delivery_time     = isset($request->delivery_time) ? $request->delivery_time :'';
            $bookingspackage->delivery_period   = isset($request->delivery_period) ? $request->delivery_period :'';
            $bookingspackage->price             = isset($request->price) ? $request->price :'';
            $bookingspackage->workspace         = getActiveWorkSpace();
            $bookingspackage->created_by        = creatorId();
            $bookingspackage->save();

            event(new CreateBookingsPackage($request,$bookingspackage));
            return redirect()->route('booking-package.index')->with('success', __('The booking package has been created successfully.'));
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
        if(\Auth::user()->isAbleTo('bookings package edit'))
        {

            $bookingspackage = BookingsPackage::where('id', $id)->where('workspace', getActiveWorkSpace())->where('created_by',creatorId())->first();
            $services = ProductService::where('type', 'bookings')->where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name','id');
            $extraservices = BookingsExtraService::where('workspace', '=', getActiveWorkSpace())->where('created_by',creatorId())->where('status','=','active')->get()->pluck('name','id');
            $periods = BookingsPackage::$periods;

            return view('bookings::package.edit',compact('bookingspackage','services','extraservices','periods'));
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
        if(\Auth::user()->isAbleTo('bookings package edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'name' => 'required',
                                'item' => 'required',
                                'services' => 'required',
                                'delivery_time' => 'required',
                                'delivery_period' => 'required',
                                'price' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $bookingspackage = BookingsPackage::find($id);
            $bookingspackage['name'] = $request->name ?? '';
            $bookingspackage['item'] = $request->item ?? '';
            $bookingspackage['services'] = implode(',', $request->input('services', []));
            $bookingspackage['delivery_time'] = $request->delivery_time ?? '';
            $bookingspackage['delivery_period'] = $request->delivery_period ?? '';
            $bookingspackage['price'] = $request->price ?? '';
            $bookingspackage['workspace'] = getActiveWorkSpace();
            $bookingspackage['created_by'] = creatorId();

            $bookingspackage->update();

            event(new UpdateBookingsPackage($request,$bookingspackage));

            return redirect()->route('booking-package.index')->with('success', __('The booking package details are updated successfully.'));
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
        if(\Auth::user()->isAbleTo('bookings package delete'))
        {
            $bookingspackage = BookingsPackage::find($id);

            if(!empty($bookingspackage))
            {
                event(new DestroyBookingsPackage($bookingspackage));
                $bookingspackage->delete();

                return redirect()->back()->with('success', 'The booking package has been deleted.');
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function duplicate($bookingspackage_id)
    {
        if (\Auth::user()->isAbleTo('bookings package duplicate')) {

            $bookingspackage                     = BookingsPackage::where('id', $bookingspackage_id)->first();

            $duplicatepackage                    = new BookingsPackage();
            $duplicatepackage->name              = isset($bookingspackage->name) ? $bookingspackage->name :'';
            $duplicatepackage->item              = isset($bookingspackage->item) ? $bookingspackage->item :'';
            $duplicatepackage->services          = isset($bookingspackage->services) ? $bookingspackage->services :'';
            $duplicatepackage->delivery_time     = isset($bookingspackage->delivery_time) ? $bookingspackage->delivery_time :'';
            $duplicatepackage->delivery_period   = isset($bookingspackage->delivery_period) ? $bookingspackage->delivery_period :'';
            $duplicatepackage->price             = isset($bookingspackage->price) ? $bookingspackage->price :'';
            $duplicatepackage->workspace         = getActiveWorkSpace();
            $duplicatepackage->created_by        = creatorId();
            $duplicatepackage->save();

            event(new DuplicateBookingsPackage($duplicatepackage, $bookingspackage));

            return redirect()->back()->with('success', __('Bookings Package Duplicate Successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function packageService(Request $request)
    {
        $packages = BookingsPackage::where('item', $request->serviceId)->get()->pluck('name','id');
        return response()->json($packages);
    }


    public function packageItemPrice(Request $request)
    {
        $data['product']     = $product = \Workdo\ProductService\Entities\ProductService::find($request->serviceId);
        $data['unit']        = !empty($product) ? ((!empty($product->unit())) ? $product->unit()->name : '') : '';
        $data['taxRate']     = $taxRate = !empty($product) ? (!empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0) : 0;
        $data['taxes']       =  !empty($product) ? (!empty($product->tax_id) ? $product->tax($product->tax_id) : 0) : 0;
        $salePrice           = !empty($product) ?  $product->sale_price : 0;
        $quantity            = 1;
        $taxPrice            = !empty($product) ? (($taxRate / 100) * ($salePrice * $quantity)) : 0;
        $data['totalAmount'] = !empty($product) ?  ($salePrice * $quantity) : 0;

        return json_encode($data);
    }
}
