<?php

namespace Workdo\Bookings\Listeners;

use Workdo\Bookings\Entities\BookingsDuration;
use Workdo\ProductService\Events\CreateProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateProductLis
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
    public function handle(CreateProduct $event)
    {
        $productService = $event->productService;
        $request        = $event->request;
        
        if (module_is_active('Bookings') && $request->type == "bookings") {
            $duration                   = new BookingsDuration();
            $duration->item_id          = $request->id;
            $duration->duration         = $productService->duration;
            $duration->created_by       = creatorId();
            $duration->workspace        = getActiveWorkSpace();
            $duration->save();
        }
    }
}
