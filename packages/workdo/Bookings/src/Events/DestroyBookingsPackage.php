<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class DestroyBookingsPackage
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $bookingspackage;

    public function __construct($bookingspackage)
    {
        $this->bookingspackage = $bookingspackage;
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
