<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class CreateInsuranceBookings
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $insurancebookings;

    public function __construct($request ,$insurancebookings)
    {
        $this->request = $request;
        $this->insurancebookings = $insurancebookings;

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
