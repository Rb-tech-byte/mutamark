<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class UpdateBookingsAppointment
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $bookingsappointment;

    public function __construct($request ,$bookingsappointment)
    {
        $this->request = $request;
        $this->bookingsappointment = $bookingsappointment;
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
