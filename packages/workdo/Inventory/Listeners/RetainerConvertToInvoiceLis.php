<?php

namespace Modules\Inventory\Listeners;

use App\Models\InvoiceProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;
use Modules\Retainer\Entities\Retainer;
use Modules\Retainer\Events\RetainerConvertToInvoice;

class RetainerConvertToInvoiceLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(RetainerConvertToInvoice $event)
    {
        $convertInvoice  = $event->convertInvoice;
        if ($convertInvoice->invoice_module == 'account') {
            $invoiceProduct = InvoiceProduct::where('invoice_id', $convertInvoice->id)->first();
            $retainer = Retainer::where('converted_invoice_id', $convertInvoice->id)->first();
            if(!empty($retainer)){
            if (!empty($invoiceProduct)) {

                $inventory = new Inventory();
                $inventory['product_id']  = $invoiceProduct->product_id;
                $inventory['type']        = 'Retainer';
                $inventory['quantity']    = $invoiceProduct->quantity;
                $inventory['description'] = $invoiceProduct->quantity . ' ' . __('Quantity decrease by') . ' ' . Retainer::retainerNumberFormat($retainer->retainer_id) . '.';
                $inventory['feild_id']    = $convertInvoice->invoice_id;
                $inventory['workspace']   = $convertInvoice->workspace;
                $inventory['created_by']  = $convertInvoice->created_by;
                $inventory->save();
            }
        }
        } else {
            return redirect()->back()->with('error', 'Retainer Not Found');
        }
    }
}
