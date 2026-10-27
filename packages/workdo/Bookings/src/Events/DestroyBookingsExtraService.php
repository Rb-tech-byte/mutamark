<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class DestroyBookingsExtraService
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $bookingextraservice;

    public function __construct($bookingextraservice)
    {
        $this->bookingextraservice = $bookingextraservice;
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
