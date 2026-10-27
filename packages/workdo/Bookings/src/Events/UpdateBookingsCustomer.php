<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class UpdateBookingsCustomer
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $bookingscustomer;

    public function __construct($request ,$bookingscustomer)
    {
        $this->request = $request;
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
