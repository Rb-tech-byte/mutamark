<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class UpdateBooking
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $booking;

    public function __construct($request ,$booking)
    {
        $this->request = $request;
        $this->booking = $booking;
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
