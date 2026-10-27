<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class DestroyBookingsAppointment
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $bookingsappointment;
    public function __construct($bookingsappointment)
    {
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
