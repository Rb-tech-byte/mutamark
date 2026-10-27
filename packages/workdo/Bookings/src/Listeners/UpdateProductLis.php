<?php

namespace Workdo\Bookings\Listeners;

use Workdo\Bookings\Entities\BookingsDuration;
use Workdo\ProductService\Events\UpdateProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductLis
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
    public function handle(UpdateProduct $event)
    {
        $productService = $event->productService;
        $request        = $event->request;
        if (module_is_active('Bookings')) {
            $duration = BookingsDuration::where('item_id', $productService->id)->first();

            if ($duration) {
                $duration->item_id  = $request->id;
                $duration->duration = $request->duration;
                $duration->save();
            }
        }
    }
}
