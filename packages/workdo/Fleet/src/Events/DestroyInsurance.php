<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyInsurance
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $insurance;

    public function __construct($insurance)
    {
        $this->insurance = $insurance;

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
