<?php

namespace Modules\Inventory\Listeners;

use App\Events\CreateInvoice;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;

class CreateInvoiceLis
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
    public function handle(CreateInvoice $event)
    {
        if (module_is_active('Inventory')) {

            $invoice = $event->invoice;
            $request = $event->request;
            if ($invoice->invoice_module == 'account') {
                $products = $request->items;
                for ($i = 0; $i < count($products); $i++) {
                    $inventory = new Inventory();
                    $inventory['product_id']  = $products[$i]['item'];
                    $inventory['type']        = 'Invoice';
                    $inventory['quantity']    = $products[$i]['quantity'];
                    $inventory['description'] =  $products[$i]['quantity'] . ' ' . __('Quantity decrease by') . ' ' . Invoice::invoiceNumberFormat($invoice->invoice_id) . '.';
                    $inventory['feild_id']    = $invoice->id;
                    $inventory['workspace']   = $invoice->workspace;
                    $inventory['created_by']  = $invoice->created_by;
                    $inventory->save();
                }
            } else {
                return redirect()->back()->with('error', 'Invoice Not Found');
            }
        }
    }
}
