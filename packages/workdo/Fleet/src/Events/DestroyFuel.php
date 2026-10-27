<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyFuel
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $fuel;

    public function __construct($fuel)
    {
        $this->fuel = $fuel;
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
