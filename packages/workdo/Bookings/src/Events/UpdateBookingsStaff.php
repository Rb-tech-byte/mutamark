<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class UpdateBookingsStaff
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $bookingstaffs;

    public function __construct($request ,$bookingstaffs)
    {
        $this->request = $request;
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
