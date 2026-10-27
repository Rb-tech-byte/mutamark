<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\InvoiceController;
use App\Models\BankTransferPayment;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Proposal;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\Bill;
use Modules\Account\Entities\BillPayment;
use Modules\Account\Http\Controllers\BillController;
use Modules\Inventory\Entities\Inventory;
use Modules\Pos\Entities\PosPayment;
use Modules\Pos\Entities\PosProduct;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use Modules\Retainer\Entities\Retainer;
use Modules\Retainer\Entities\RetainerPayment;
use Modules\Sales\Entities\SalesInvoice;
use Modules\Sales\Entities\SalesInvoiceItem;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (module_is_active('Inventory')) {
            if (\Auth::user()->isAbleTo('inventory manage')) {
                $inventorys = Inventory::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->orderBy('id', 'desc')->get();
                return view('inventory::inventory.index', compact('inventorys'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('inventory::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($feild_id, $type)
    {
        if (\Auth::user()->isAbleTo('inventory show')) {
            if ($type == 'Bill') {
                return redirect()->route('bill.show', encrypt($feild_id));
            } elseif ($type == 'Retainer') {
                return redirect()->route('invoice.show', encrypt($feild_id));
            } elseif ($type == 'Proposal') {
                return redirect()->route('invoice.show', encrypt($feild_id));
            } elseif ($type == 'Purchase') {
                return redirect()->route('purchases.show', encrypt($feild_id));
            } elseif ($type == 'Invoice') {
                return redirect()->route('invoice.show', encrypt($feild_id));
            } elseif ($type == 'POS Invoice') {
                return redirect()->route('pos.show', encrypt($feild_id));
            } elseif ($type == 'Sales Invoice') {
                $salesInvoiceItems = SalesInvoiceItem::find($feild_id);
                $salesInvoice = SalesInvoice::find($salesInvoiceItems->invoice_id);
                return redirect()->route('salesinvoice.show', $salesInvoice->id);
            } else {
                return abort('404', 'Not Found');
            }
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
        return view('inventory::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
