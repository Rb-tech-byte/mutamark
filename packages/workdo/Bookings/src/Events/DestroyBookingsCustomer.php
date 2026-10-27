<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class DestroyBookingsCustomer
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $bookingscustomer;

    public function __construct($bookingscustomer)
    {
        $this->bookingscustomer = $bookingscustomer;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
