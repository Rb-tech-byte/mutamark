<?php

namespace Workdo\Fleet\Listeners;

use App\Events\CreateInvoice;
use Workdo\Fleet\Entities\VehicleInvoice;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $invoice = $event->invoice;
        $request = $event->request;

        if(module_is_active('Fleet') && $invoice->account_type == 'Fleet'){

            $products = $request->items;

            foreach ($products as $productData) {
                $VehicleInvoice = new VehicleInvoice();
                $VehicleInvoice->invoice_id  = $invoice->id;
                $VehicleInvoice->product_type  = $productData['product_type'];
                $VehicleInvoice->item  = $productData['product_id'];
                $VehicleInvoice->start_location  = $productData['start_location'];
                $VehicleInvoice->end_location  = $productData['end_location'];
                $VehicleInvoice->trip_type  = $productData['trip_type'];
                $VehicleInvoice->rate  = $productData['rate'];
                $VehicleInvoice->start_date  = $productData['start_date'];
                $VehicleInvoice->end_date  = $productData['end_date'];
                $VehicleInvoice->distance  = $productData['distance'];
                $VehicleInvoice->description  = $productData['description'];
                $VehicleInvoice->save();
            }
        }

    }
}
