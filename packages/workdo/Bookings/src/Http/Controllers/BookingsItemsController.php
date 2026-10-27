<?php

namespace Workdo\Bookings\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\ProductService\Entities\ProductService;
use Workdo\Bookings\DataTables\ItemsDataTable;

class BookingsItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ItemsDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('bookings items manage'))
        {
            return $dataTable->render('bookings::product_items.index');
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
        return redirect()->back();
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

    public function description($id)
    {
        if (\Auth::user()->isAbleTo('bookings description show')) {
            $items = ProductService::find($id);
            return view('bookings::product_items.description', compact('items'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
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
