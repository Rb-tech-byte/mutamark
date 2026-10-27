<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class DuplicateBookingsPackage
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $duplicatepackage;
    public $bookingspackage;

    public function __construct($duplicatepackage, $bookingspackage = null)
    {
        $this->duplicatepackage    = $duplicatepackage;
        $this->bookingspackage     = $bookingspackage;
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
