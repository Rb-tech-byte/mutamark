<?php

namespace Workdo\Bookings\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use Workdo\Bookings\Listeners\CompanyMenuListener; 
use Workdo\ProductService\Events\UpdateProduct;
use Workdo\Bookings\Listeners\UpdateProductLis;
use Workdo\ProductService\Events\CreateProduct;
use Workdo\Bookings\Listeners\CreateProductLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        UpdateProduct::class => [
            UpdateProductLis::class,
        ],
        CreateProduct::class => [
            CreateProductLis::class,
        ],
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
