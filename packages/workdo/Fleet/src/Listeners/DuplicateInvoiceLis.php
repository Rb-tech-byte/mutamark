<?php

namespace Workdo\Fleet\Listeners;

use Workdo\Fleet\Entities\VehicleInvoice;
use App\Events\DuplicateInvoice;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Invoice;

class DuplicateInvoiceLis
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
    public function handle(DuplicateInvoice $event)
    {
        $duplicateInvoice = $event->duplicateInvoice;
        $invoice = $event->invoice;
        
        $products = VehicleInvoice::where('invoice_id',$invoice->id)->get();
        foreach ($products as $productData) {

            $VehicleInvoice = new VehicleInvoice();
            $VehicleInvoice->invoice_id  = $duplicateInvoice->id;
            $VehicleInvoice->product_type  = $productData->product_type;
            $VehicleInvoice->item  = $productData->item;
            $VehicleInvoice->start_location  = $productData->start_location;
            $VehicleInvoice->end_location  = $productData->end_location;
            $VehicleInvoice->trip_type  = $productData->trip_type;
            $VehicleInvoice->rate  = $productData->rate;
            $VehicleInvoice->start_date  = $productData->start_date;
            $VehicleInvoice->end_date  = $productData->end_date;
            $VehicleInvoice->distance  = $productData->distance;
            $VehicleInvoice->description  = $productData->description;
            $VehicleInvoice->save();
        }

    }

}
