<?php

namespace Modules\Inventory\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;
use Modules\Sales\Entities\SalesInvoice;
use Modules\Sales\Entities\SalesInvoiceItem;
use Modules\Sales\Events\CreateSalesInvoiceItem;

class CreateSalesInvoiceItemLis
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
    public function handle(CreateSalesInvoiceItem $event)
    {
        if (module_is_active('Inventory')) {
            $invoice = $event->invoice;
            $request = $event->request;
            $invoiceitem = SalesInvoiceItem::where('invoice_id',$invoice->invoice_id)->first();

            $inventory = new Inventory();
            $inventory['product_id']  = $invoiceitem->item;
            $inventory['type']        = 'Sales Invoice';
            $inventory['quantity']    = $invoiceitem->quantity;
            $inventory['description'] =  $invoiceitem->quantity . ' ' . __('Quantity decrease by') . ' ' . SalesInvoice::invoiceNumberFormat($invoice->id) . '.';
            $inventory['feild_id']    = $invoiceitem->id;
            $inventory['workspace']   = $invoiceitem->workspace;
            $inventory['created_by']  = $invoiceitem->created_by;
            $inventory->save();
        }
    }
}
