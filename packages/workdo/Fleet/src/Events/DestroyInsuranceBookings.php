<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyInsuranceBookings
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $insurances;

    public function __construct($insurances)
    {
        $this->insurances = $insurances;

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
