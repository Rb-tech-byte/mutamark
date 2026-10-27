<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class DestroyBookingsStaff
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $bookingstaffs;

    public function __construct($bookingstaffs)
    {
        $this->bookingstaffs = $bookingstaffs;
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
