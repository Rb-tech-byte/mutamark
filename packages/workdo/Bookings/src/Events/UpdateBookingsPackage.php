<?php

namespace Workdo\Bookings\Events;

use Illuminate\Queue\SerializesModels;

class UpdateBookingsPackage
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $bookingextraservice;

    public function __construct($request ,$bookingextraservice)
    {
        $this->request = $request;
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
