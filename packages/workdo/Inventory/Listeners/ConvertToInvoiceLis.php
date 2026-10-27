<?php

namespace Modules\Inventory\Listeners;

use App\Events\ConvertToInvoice;
use App\Models\InvoiceProduct;
use App\Models\Proposal;
use App\Models\ProposalProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Inventory\Entities\Inventory;

class ConvertToInvoiceLis
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
    public function handle(ConvertToInvoice $event)
    {
        if (module_is_active('Inventory')) {
            $convertInvoice   = $event->convertInvoice;
            if ($convertInvoice->invoice_module == 'account') {
                $invoiceProduct = InvoiceProduct::where('invoice_id', $convertInvoice->id)->first();
                $proposal = Proposal::where('converted_invoice_id', $convertInvoice->id)->first();
                if ($proposal) {
                    $inventory = new Inventory([
                        'product_id' => $invoiceProduct->product_id,
                        'type' => 'Proposal',
                        'quantity' => $invoiceProduct->quantity,
                        'description' => $invoiceProduct->quantity . __('Quantity decrease by') . ' ' . Proposal::proposalNumberFormat($proposal->proposal_id) . '.',
                        'feild_id' => $convertInvoice->invoice_id,
                        'workspace' => $convertInvoice->workspace,
                        'created_by' => $convertInvoice->created_by,
                    ]);

                    $inventory->save();
                } else {
                    return redirect()->back()->with('error', 'Proposal Not Found');
                }
            } else {
                return redirect()->back()->with('error', 'Proposal Account Not Found');
            }
        }
    }
}
