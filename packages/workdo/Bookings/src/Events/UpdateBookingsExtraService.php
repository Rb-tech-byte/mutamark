<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class UpdateBookingsExtraService
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $bookingspackage;

    public function __construct($request ,$bookingspackage)
    {
        $this->request = $request;
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
